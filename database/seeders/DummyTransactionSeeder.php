<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Journal;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates 3 months of dummy transactions (Oct, Nov, Dec 2025)
     */
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $this->command->error('No company found. Please run setup wizard first.');
            return;
        }

        // Get key accounts
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_parent', false)
            ->get()
            ->keyBy('code');

        // Define account mappings - adjust codes based on your Chart of Accounts
        $cashCode = '1.1.1.01';       // Kas
        $bankCode = '1.1.2.01';       // Bank  
        $arCode = '1.1.3.01';         // Piutang Usaha
        $apCode = '2.1.1.01';         // Hutang Usaha
        $revenueCode = '4.1.1.01';    // Pendapatan Penjualan
        $cogsCode = '5.1.1.01';       // HPP
        $salaryCode = '5.2.1.01';     // Beban Gaji
        $rentCode = '5.2.2.01';       // Beban Sewa
        $utilityCode = '5.2.3.01';    // Beban Listrik & Air
        $officeCode = '5.2.4.01';     // Beban Perlengkapan Kantor
        $capitalCode = '3.1.1.01';    // Modal

        // Try to find accounts by code prefix
        $cash = $this->findAccount($accounts, ['1.1.1', 'Kas', 'kas']);
        $bank = $this->findAccount($accounts, ['1.1.2', 'Bank', 'bank']);
        $ar = $this->findAccount($accounts, ['1.1.3', 'Piutang', 'piutang']);
        $ap = $this->findAccount($accounts, ['2.1.1', 'Hutang', 'hutang']);
        $revenue = $this->findAccount($accounts, ['4.1.1', 'Pendapatan', 'penjualan']);
        $cogs = $this->findAccount($accounts, ['5.1.1', 'HPP', 'harga pokok']);
        $salary = $this->findAccount($accounts, ['5.2.1', 'Gaji', 'gaji']);
        $rent = $this->findAccount($accounts, ['5.2.2', 'Sewa', 'sewa']);
        $utility = $this->findAccount($accounts, ['5.2.3', 'Listrik', 'listrik']);
        $office = $this->findAccount($accounts, ['5.2.4', 'Perlengkapan', 'ATK']);

        if (!$cash || !$revenue) {
            $this->command->error('Required accounts not found. Please check your Chart of Accounts.');
            $this->command->info('Available accounts:');
            foreach ($accounts as $acc) {
                $this->command->info(" - {$acc->code}: {$acc->name}");
            }
            return;
        }

        $this->command->info("Using accounts:");
        $this->command->info("  Cash: " . ($cash ? $cash->code : 'N/A'));
        $this->command->info("  Bank: " . ($bank ? $bank->code : 'N/A'));
        $this->command->info("  Revenue: " . ($revenue ? $revenue->code : 'N/A'));

        $months = [
            ['year' => 2025, 'month' => 10, 'name' => 'Oktober'],
            ['year' => 2025, 'month' => 11, 'name' => 'November'],
            ['year' => 2025, 'month' => 12, 'name' => 'Desember'],
        ];

        foreach ($months as $m) {
            $this->generateMonthlyTransactions($company, $m, [
                'cash' => $cash,
                'bank' => $bank,
                'ar' => $ar,
                'ap' => $ap,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'salary' => $salary,
                'rent' => $rent,
                'utility' => $utility,
                'office' => $office,
            ]);
        }

        $this->command->info('Dummy transactions created successfully!');
    }

    private function findAccount($accounts, array $searchTerms)
    {
        foreach ($accounts as $account) {
            foreach ($searchTerms as $term) {
                if (str_contains(strtolower($account->code), strtolower($term)) ||
                    str_contains(strtolower($account->name), strtolower($term))) {
                    return $account;
                }
            }
        }
        return null;
    }

    private function generateMonthlyTransactions($company, $month, $accounts): void
    {
        $year = $month['year'];
        $m = $month['month'];
        $monthName = $month['name'];

        $this->command->info("Generating transactions for {$monthName} {$year}...");

        // === TRANSACTION 1: Initial Capital / Beginning Balance (only first month) ===
        if ($m == 10 && $accounts['cash']) {
            $this->createJournal($company, [
                'date' => Carbon::create($year, $m, 1),
                'description' => 'Saldo Awal Kas',
                'reference' => 'SA-' . $year . sprintf('%02d', $m) . '001',
                'source' => 'manual',
                'items' => [
                    ['account' => $accounts['cash'], 'debit' => 50000000, 'credit' => 0],
                    ['account' => $accounts['revenue'] ?? $accounts['cash'], 'debit' => 0, 'credit' => 50000000],
                ],
            ]);
        }

        // === SALES TRANSACTIONS (8-12 per month) ===
        $salesCount = rand(8, 12);
        for ($i = 1; $i <= $salesCount; $i++) {
            $day = rand(1, 28);
            $amount = rand(1, 20) * 500000; // 500K - 10M
            
            $this->createJournal($company, [
                'date' => Carbon::create($year, $m, $day),
                'description' => "Penjualan #{$i} - Customer " . chr(64 + rand(1, 10)),
                'reference' => 'SL-' . $year . sprintf('%02d', $m) . sprintf('%03d', $i),
                'source' => 'manual',
                'items' => [
                    ['account' => $accounts['cash'] ?? $accounts['bank'], 'debit' => $amount, 'credit' => 0],
                    ['account' => $accounts['revenue'], 'debit' => 0, 'credit' => $amount],
                ],
            ]);
        }

        // === EXPENSE: Salary (1x per month) ===
        if ($accounts['salary'] && $accounts['cash']) {
            $salaryAmount = rand(8, 15) * 1000000;
            $this->createJournal($company, [
                'date' => Carbon::create($year, $m, 25),
                'description' => "Pembayaran Gaji Karyawan Bulan {$monthName}",
                'reference' => 'EX-' . $year . sprintf('%02d', $m) . '001',
                'source' => 'manual',
                'items' => [
                    ['account' => $accounts['salary'], 'debit' => $salaryAmount, 'credit' => 0],
                    ['account' => $accounts['cash'], 'debit' => 0, 'credit' => $salaryAmount],
                ],
            ]);
        }

        // === EXPENSE: Rent (1x per month) ===
        if ($accounts['rent'] && $accounts['cash']) {
            $rentAmount = rand(3, 5) * 1000000;
            $this->createJournal($company, [
                'date' => Carbon::create($year, $m, 5),
                'description' => "Pembayaran Sewa Kantor Bulan {$monthName}",
                'reference' => 'EX-' . $year . sprintf('%02d', $m) . '002',
                'source' => 'manual',
                'items' => [
                    ['account' => $accounts['rent'], 'debit' => $rentAmount, 'credit' => 0],
                    ['account' => $accounts['cash'], 'debit' => 0, 'credit' => $rentAmount],
                ],
            ]);
        }

        // === EXPENSE: Utility (1x per month) ===
        if ($accounts['utility'] && $accounts['cash']) {
            $utilityAmount = rand(5, 15) * 100000;
            $this->createJournal($company, [
                'date' => Carbon::create($year, $m, 20),
                'description' => "Pembayaran Listrik & Air Bulan {$monthName}",
                'reference' => 'EX-' . $year . sprintf('%02d', $m) . '003',
                'source' => 'manual',
                'items' => [
                    ['account' => $accounts['utility'], 'debit' => $utilityAmount, 'credit' => 0],
                    ['account' => $accounts['cash'], 'debit' => 0, 'credit' => $utilityAmount],
                ],
            ]);
        }

        // === EXPENSE: Office Supplies (2-3x per month) ===
        if ($accounts['office'] && $accounts['cash']) {
            $officeCount = rand(2, 3);
            for ($i = 1; $i <= $officeCount; $i++) {
                $day = rand(1, 28);
                $officeAmount = rand(1, 5) * 100000;
                $this->createJournal($company, [
                    'date' => Carbon::create($year, $m, $day),
                    'description' => "Pembelian Perlengkapan Kantor #{$i}",
                    'reference' => 'EX-' . $year . sprintf('%02d', $m) . sprintf('%03d', 10 + $i),
                    'source' => 'manual',
                    'items' => [
                        ['account' => $accounts['office'], 'debit' => $officeAmount, 'credit' => 0],
                        ['account' => $accounts['cash'], 'debit' => 0, 'credit' => $officeAmount],
                    ],
                ]);
            }
        }
    }

    private function createJournal($company, array $data): void
    {
        DB::transaction(function () use ($company, $data) {
            $journal = Journal::create([
                'company_id' => $company->id,
                'business_unit_id' => null,
                'date' => $data['date'],
                'reference' => $data['reference'],
                'description' => $data['description'],
                'source' => $data['source'] ?? 'seeder',
                'is_posted' => true,
            ]);

            foreach ($data['items'] as $item) {
                if ($item['account']) {
                    JournalItem::create([
                        'journal_id' => $journal->id,
                        'coa_id' => $item['account']->id,
                        'debit' => $item['debit'],
                        'credit' => $item['credit'],
                    ]);
                }
            }
        });
    }
}
