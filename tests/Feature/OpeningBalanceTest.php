<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CoaUmkmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningBalanceTest extends TestCase
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

    public function test_opening_balance_is_included_in_get_balance_when_start_date_is_null(): void
    {
        // Get an Asset account (normal balance DEBIT)
        $account = ChartOfAccount::where('company_id', $this->company->id)
            ->where('type', 'Asset')
            ->where('normal_balance', 'DEBIT')
            ->where('is_parent', false)
            ->first();

        // Set opening balance
        $account->update(['opening_balance' => 500000]);

        // Get balance without start date (cumulative)
        $this->assertEquals(500000, $account->getBalance());

        // Get balance with start date (should not include opening balance)
        $this->assertEquals(0, $account->getBalance(now()->format('Y-m-d')));
    }

    public function test_opening_balance_in_balance_sheet_report(): void
    {
        // Get an Asset account
        $account = ChartOfAccount::where('company_id', $this->company->id)
            ->where('type', 'Asset')
            ->where('normal_balance', 'DEBIT')
            ->where('is_parent', false)
            ->first();

        $account->update(['opening_balance' => 750000]);

        // Query Balance Sheet (Neraca)
        $response = $this->actingAs($this->user)
            ->withoutMiddleware([
                \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])
            ->getJson('/reports/balance-sheet');

        $response->assertStatus(200);
        
        // Assert that the account has the correct balance including opening balance
        $data = $response->json('data.sections.Aset');
        $found = false;
        foreach ($data as $item) {
            if ($item['account_code'] === $account->code) {
                $this->assertEquals(750000, $item['balance']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Account not found in Asset section of balance sheet");
        
        // Assert total Assets also includes it
        $this->assertEquals(750000, $response->json('data.totals.Aset'));
    }
}
