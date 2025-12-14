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
        $response = $this->actingAs($this->user)->postJson('/journals/manual', [
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
        $response = $this->actingAs($this->user)->postJson('/journals/manual', [
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
            'is_posted' => true,
        ]);
    }

    public function test_operator_cannot_create_manual_journal(): void
    {
        $this->user->update(['role' => 'Operator']);

        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        $response = $this->actingAs($this->user)->postJson('/journals/manual', [
            'date' => now()->format('Y-m-d'),
            'description' => 'Test Journal',
            'lines' => [
                ['account_id' => $accounts[0]->id, 'debit' => 100000, 'credit' => 0],
                ['account_id' => $accounts[1]->id, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $response->assertStatus(403);
    }
}
