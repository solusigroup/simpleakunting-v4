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

class BalanceSheetAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

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

        (new CoaUmkmSeeder())->run($this->company);
    }

    public function test_balance_sheet_analysis_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->get('/reports/balance-sheet-analysis');

        $response->assertStatus(200);
        $response->assertSee('Analisa Keseimbangan Neraca');
        $response->assertSee('SEIMBANG');
    }

    public function test_balance_sheet_analysis_flags_unbalanced_journals(): void
    {
        $accounts = ChartOfAccount::where('company_id', $this->company->id)
            ->where('is_parent', false)
            ->take(2)
            ->get();

        // Create an unbalanced journal entry directly in DB (bypass validation)
        $journal = Journal::create([
            'company_id' => $this->company->id,
            'date' => now()->format('Y-m-d'),
            'reference' => 'UNBAL-001',
            'description' => 'Unbalanced Journal Entry',
            'source' => 'manual',
            'is_posted' => true,
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
            'credit' => 80000, // Imbalance of 20000
        ]);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->get('/reports/balance-sheet-analysis');

        $response->assertStatus(200);
        $response->assertSee('TIDAK SEIMBANG');
        $response->assertSee('UNBAL-001');
        $response->assertSee('Unbalanced Journal Entry');
    }

    public function test_balance_sheet_analysis_flags_opening_balance_mismatch(): void
    {
        // Get one Debit account and one Credit account
        $debitAcc = ChartOfAccount::where('company_id', $this->company->id)
            ->where('normal_balance', 'DEBIT')
            ->where('is_parent', false)
            ->first();

        $creditAcc = ChartOfAccount::where('company_id', $this->company->id)
            ->where('normal_balance', 'KREDIT')
            ->where('is_parent', false)
            ->first();

        // Set mismatched opening balances
        $debitAcc->update(['opening_balance' => 500000]);
        $creditAcc->update(['opening_balance' => 450000]); // 50000 difference

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->get('/reports/balance-sheet-analysis');

        $response->assertStatus(200);
        $response->assertSee('Saldo awal tidak seimbang');
        $response->assertSee(number_format(50000, 2, ',', '.'));
    }

    public function test_balance_sheet_analysis_flags_misconfigured_coa(): void
    {
        // Get an Asset account
        $account = ChartOfAccount::where('company_id', $this->company->id)
            ->where('type', 'Asset')
            ->first();

        // Misconfigure report_type to LABARUGI
        $account->update(['report_type' => 'LABARUGI']);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->get('/reports/balance-sheet-analysis');

        $response->assertStatus(200);
        $response->assertSee('Ditemukan akun dengan pemetaan tipe laporan yang salah');
        $response->assertSee($account->code);
    }
}
