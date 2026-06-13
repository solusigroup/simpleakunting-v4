<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\User;
use Database\Seeders\CoaUmkmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user and company
        $this->user = User::factory()->create([
            'role' => 'Administrator',
        ]);
        
        $this->company = Company::create([
            'user_id' => $this->user->id,
            'name' => 'Test Company',
            'entity_type' => 'UMKM',
            'fiscal_start' => now()->startOfYear(),
        ]);
        
        $this->user->update(['company_id' => $this->company->id]);
        
        // Seed COA
        (new CoaUmkmSeeder())->run($this->company);
    }

    public function test_manual_journal_requires_balanced_entries(): void
    {
        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        // Unbalanced journal should fail
        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->postJson('/journals/manual', [
                'date' => now()->format('Y-m-d'),
                'description' => 'Test Journal',
                'lines' => [
                    ['account_id' => $accounts[0]->id, 'debit' => 100000, 'credit' => 0],
                    ['account_id' => $accounts[1]->id, 'debit' => 0, 'credit' => 50000], // Not balanced!
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_manual_journal_creates_balanced_entries(): void
    {
        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        // Balanced journal should succeed
        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->postJson('/journals/manual', [
                'date' => now()->format('Y-m-d'),
                'description' => 'Test Balanced Journal',
                'lines' => [
                    ['account_id' => $accounts[0]->id, 'debit' => 100000, 'credit' => 0],
                    ['account_id' => $accounts[1]->id, 'debit' => 0, 'credit' => 100000],
                ],
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        // Verify journal was created
        $this->assertDatabaseHas('journals', [
            'company_id' => $this->company->id,
            'description' => 'Test Balanced Journal',
            'source' => 'manual',
            'is_posted' => false,
        ]);
    }

    public function test_operator_cannot_create_manual_journal(): void
    {
        $this->user->update(['role' => 'Operator']);

        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->postJson('/journals/manual', [
                'date' => now()->format('Y-m-d'),
                'description' => 'Test Journal',
                'lines' => [
                    ['account_id' => $accounts[0]->id, 'debit' => 100000, 'credit' => 0],
                    ['account_id' => $accounts[1]->id, 'debit' => 0, 'credit' => 100000],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_or_manager_can_bulk_post_journals(): void
    {
        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        // Create 2 draft journals
        $journal1 = Journal::create([
            'company_id' => $this->company->id,
            'date' => now()->format('Y-m-d'),
            'reference' => 'TEST-001',
            'description' => 'Draft Journal 1',
            'source' => 'manual',
            'is_posted' => false,
        ]);
        JournalItem::create([
            'journal_id' => $journal1->id,
            'coa_id' => $accounts[0]->id,
            'debit' => 100000,
            'credit' => 0,
        ]);
        JournalItem::create([
            'journal_id' => $journal1->id,
            'coa_id' => $accounts[1]->id,
            'debit' => 0,
            'credit' => 100000,
        ]);

        $journal2 = Journal::create([
            'company_id' => $this->company->id,
            'date' => now()->format('Y-m-d'),
            'reference' => 'TEST-002',
            'description' => 'Draft Journal 2',
            'source' => 'manual',
            'is_posted' => false,
        ]);
        JournalItem::create([
            'journal_id' => $journal2->id,
            'coa_id' => $accounts[0]->id,
            'debit' => 50000,
            'credit' => 0,
        ]);
        JournalItem::create([
            'journal_id' => $journal2->id,
            'coa_id' => $accounts[1]->id,
            'debit' => 0,
            'credit' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->postJson('/journals/bulk-post', [
                'ids' => [$journal1->id, $journal2->id],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertTrue($journal1->fresh()->is_posted);
        $this->assertTrue($journal2->fresh()->is_posted);
    }

    public function test_operator_cannot_bulk_post_journals(): void
    {
        $this->user->update(['role' => 'Operator']);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->postJson('/journals/bulk-post', [
                'ids' => [1, 2],
            ]);

        $response->assertStatus(403);
    }

    public function test_unbalanced_journals_failed_bulk_post(): void
    {
        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        // Create unbalanced draft journal
        $journal = Journal::create([
            'company_id' => $this->company->id,
            'date' => now()->format('Y-m-d'),
            'reference' => 'TEST-UNB',
            'description' => 'Unbalanced Draft Journal',
            'source' => 'manual',
            'is_posted' => false,
        ]);
        JournalItem::create([
            'journal_id' => $journal->id,
            'coa_id' => $accounts[0]->id,
            'debit' => 100000,
            'credit' => 0,
        ]);
        JournalItem::create([
            'journal_id' => $journal->id,
            'coa_id' => $accounts[1]->id,
            'debit' => 0,
            'credit' => 50000, // Unbalanced
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->postJson('/journals/bulk-post', [
                'ids' => [$journal->id],
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertFalse($journal->fresh()->is_posted);
    }
}
