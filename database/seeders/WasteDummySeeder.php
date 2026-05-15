<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\ChartOfAccount;
use App\Models\WasteCategory;
use App\Models\WasteCollector;
use App\Models\WasteDeposit;
use App\Models\WasteSale;
use App\Models\WasteWithdrawal;
use App\Models\Journal;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;

class WasteDummySeeder extends Seeder
{
    public function run(): void
    {
        // Find suitable accounts
        $cash = ChartOfAccount::where('code', 'like', '1.1.1%')->orWhere('name', 'like', '%Kas%')->first();
        $inventory = ChartOfAccount::where('code', 'like', '1.1.4%')->orWhere('name', 'like', '%Persediaan%')->first();
        $liability = ChartOfAccount::where('code', 'like', '2.1.1%')->orWhere('name', 'like', '%Utang%')->first();
        $revenue = ChartOfAccount::where('code', 'like', '4.1.1%')->orWhere('name', 'like', '%Pendapatan%')->first();
        $cogs = ChartOfAccount::where('code', 'like', '5.1.1%')->orWhere('name', 'like', '%HPP%')->orWhere('name', 'like', '%Harga Pokok%')->first();

        $company = Company::first();
        if ($company) {
            $company->update([
                'waste_cash_account_id' => $cash->id ?? null,
                'waste_inventory_account_id' => $inventory->id ?? null,
                'waste_liability_account_id' => $liability->id ?? null,
                'waste_revenue_account_id' => $revenue->id ?? null,
                'waste_cogs_account_id' => $cogs->id ?? null,
            ]);
        }

        // Create Categories
        $cats = [
            ['name' => 'Plastik PET', 'unit' => 'kg', 'buy_price' => 2500, 'sell_price' => 4000],
            ['name' => 'Kertas Koran', 'unit' => 'kg', 'buy_price' => 1500, 'sell_price' => 2200],
            ['name' => 'Kardus Bekas', 'unit' => 'kg', 'buy_price' => 2000, 'sell_price' => 3100],
            ['name' => 'Logam/Besi', 'unit' => 'kg', 'buy_price' => 4500, 'sell_price' => 6000],
        ];

        foreach ($cats as $cat) {
            WasteCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // Create Collectors
        $collectors = [
            ['name' => 'Budi Santoso', 'phone' => '08123456789', 'address' => 'Jl. Merdeka No. 1'],
            ['name' => 'Siti Aminah', 'phone' => '08198765432', 'address' => 'Gg. Kelinci No. 5'],
            ['name' => 'Agus Hermawan', 'phone' => '08561234567', 'address' => 'Perum Indah Blok C1'],
        ];

        foreach ($collectors as $i => $coll) {
            $coll['collector_number'] = 'NSB-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            WasteCollector::updateOrCreate(['collector_number' => $coll['collector_number']], $coll);
        }

        // Transactions
        if ($company && $company->waste_inventory_account_id && $company->waste_liability_account_id) {
            $all_cats = WasteCategory::all();
            $all_colls = WasteCollector::all();

            // 1. Deposits
            foreach ($all_colls as $coll) {
                foreach ($all_cats->random(2) as $cat) {
                    $weight = rand(5, 20);
                    $amount = $weight * $cat->buy_price;
                    $ref = 'DEP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT);

                    DB::transaction(function() use ($coll, $cat, $weight, $amount, $ref, $company) {
                        $journal = Journal::create([
                            'company_id' => $company->id,
                            'date' => now()->subDays(rand(1, 10)),
                            'reference' => $ref,
                            'description' => "Setoran sampah: {$coll->name} - {$cat->name}",
                            'source' => 'waste_bank',
                            'is_posted' => true,
                        ]);

                        JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_inventory_account_id, 'debit' => $amount, 'credit' => 0, 'memo' => "Stok: {$cat->name}"]);
                        JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_liability_account_id, 'debit' => 0, 'credit' => $amount, 'memo' => "Utang: {$coll->name}"]);

                        WasteDeposit::create([
                            'deposit_number' => $ref,
                            'waste_collector_id' => $coll->id,
                            'waste_category_id' => $cat->id,
                            'weight' => $weight,
                            'price_at_time' => $cat->buy_price,
                            'total_amount' => $amount,
                            'date' => $journal->date,
                            'journal_id' => $journal->id,
                        ]);

                        $coll->increment('balance', $amount);
                    });
                }
            }

            // 2. Sales (to Agregator)
            if ($company->waste_cash_account_id && $company->waste_revenue_account_id && $company->waste_cogs_account_id) {
                foreach ($all_cats->random(2) as $cat) {
                    $weight = 5;
                    $revenue_amt = $weight * $cat->sell_price;
                    $cost_amt = $weight * $cat->buy_price;
                    $ref = 'SLS-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT);

                    DB::transaction(function() use ($cat, $weight, $revenue_amt, $cost_amt, $ref, $company) {
                        $journal = Journal::create([
                            'company_id' => $company->id,
                            'date' => now()->subDays(rand(1, 3)),
                            'reference' => $ref,
                            'description' => "Penjualan sampah ke Agregator: {$cat->name}",
                            'source' => 'waste_bank',
                            'is_posted' => true,
                        ]);

                        JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_cash_account_id, 'debit' => $revenue_amt, 'credit' => 0]);
                        JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_revenue_account_id, 'debit' => 0, 'credit' => $revenue_amt]);
                        JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_cogs_account_id, 'debit' => $cost_amt, 'credit' => 0]);
                        JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_inventory_account_id, 'debit' => 0, 'credit' => $cost_amt]);

                        WasteSale::create([
                            'sale_number' => $ref,
                            'waste_category_id' => $cat->id,
                            'weight' => $weight,
                            'price_at_time' => $cat->sell_price,
                            'total_amount' => $revenue_amt,
                            'date' => $journal->date,
                            'buyer_name' => 'Agregator Pusat',
                            'journal_id' => $journal->id,
                        ]);
                    });
                }
            }

            // 3. Withdrawals
            if ($company->waste_cash_account_id) {
                $coll = $all_colls->first();
                $amount = 10000;
                $ref = 'WTH-' . now()->format('Ymd') . '-0001';

                DB::transaction(function() use ($coll, $amount, $ref, $company) {
                    $journal = Journal::create([
                        'company_id' => $company->id,
                        'date' => now(),
                        'reference' => $ref,
                        'description' => "Penarikan tabungan: {$coll->name}",
                        'source' => 'waste_bank',
                        'is_posted' => true,
                    ]);

                    JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_liability_account_id, 'debit' => $amount, 'credit' => 0]);
                    JournalItem::create(['journal_id' => $journal->id, 'coa_id' => $company->waste_cash_account_id, 'debit' => 0, 'credit' => $amount]);

                    WasteWithdrawal::create([
                        'withdrawal_number' => $ref,
                        'waste_collector_id' => $coll->id,
                        'amount' => $amount,
                        'date' => now(),
                        'journal_id' => $journal->id,
                    ]);

                    $coll->decrement('balance', $amount);
                });
            }
        }
    }
}
