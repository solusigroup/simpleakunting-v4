<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CoaUmkmSeeder extends Seeder
{
    /**
     * Seed Chart of Accounts for UMKM (SAK EP).
     * Schema B: Kode format 1000, 1100, 1200, etc.
     */
    public function run(Company $company): void
    {
        $accounts = [
            // ASSETS (1000-1999)
            ['code' => '1000', 'name' => 'ASET', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => null, 'category' => 'general'],
            ['code' => '1100', 'name' => 'Kas & Bank', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000', 'category' => 'cash_bank'],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000', 'category' => 'accounts_receivable'],
            ['code' => '1300', 'name' => 'Persediaan Barang', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000', 'category' => 'inventory'],
            ['code' => '1500', 'name' => 'Aset Tetap', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000', 'category' => 'fixed_asset'],
            ['code' => '1599', 'name' => 'Akumulasi Penyusutan', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '1000', 'category' => 'accumulated_depreciation'],
            
            // LIABILITIES (2000-2999)
            ['code' => '2000', 'name' => 'KEWAJIBAN', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true, 'parent' => null, 'category' => 'general'],
            ['code' => '2100', 'name' => 'Utang Usaha', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '2000', 'category' => 'accounts_payable'],
            ['code' => '2200', 'name' => 'Utang Bank', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '2000', 'category' => 'long_term_liability'],
            
            // EQUITY (3000-3999)
            ['code' => '3000', 'name' => 'EKUITAS', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true, 'parent' => null, 'category' => 'equity_other'],
            ['code' => '3100', 'name' => 'Modal Pemilik', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '3000', 'category' => 'equity_capital'],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '3000', 'category' => 'equity_retained'],
            ['code' => '3300', 'name' => 'Prive (Penarikan Modal)', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '3000', 'category' => 'equity_other'],
            
            // REVENUE (4000-4999)
            ['code' => '4000', 'name' => 'PENDAPATAN', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => true, 'parent' => null, 'category' => 'general'],
            ['code' => '4100', 'name' => 'Penjualan Barang/Jasa', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '4000', 'category' => 'revenue_sales'],
            ['code' => '4200', 'name' => 'Potongan Penjualan', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '4000', 'category' => 'revenue_sales'],
            
            // COGS / HPP (5000-5999)
            ['code' => '5000', 'name' => 'HARGA POKOK PENJUALAN', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => null, 'category' => 'cogs'],
            ['code' => '5100', 'name' => 'Beban Pokok Pendapatan', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '5000', 'category' => 'cogs'],
            
            // EXPENSES (6000-6999)
            ['code' => '6000', 'name' => 'BEBAN OPERASIONAL', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => null, 'category' => 'expense_operational'],
            ['code' => '6100', 'name' => 'Gaji & Upah', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000', 'category' => 'expense_operational'],
            ['code' => '6200', 'name' => 'Sewa Bangunan', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000', 'category' => 'expense_operational'],
            ['code' => '6300', 'name' => 'Listrik, Air & Telepon', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000', 'category' => 'expense_operational'],
            ['code' => '6400', 'name' => 'Perlengkapan (ATK)', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000', 'category' => 'expense_operational'],
        ];

        // PSAK 69 (Biological Assets) Accounts
        if ($company->enable_psak69) {
            $psak69Accounts = [
                // Assets
                ['code' => '1600', 'name' => 'ASET BIOLOGIS', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => '1000', 'category' => 'biological_asset'],
                ['code' => '1610', 'name' => 'Aset Biologis (Ternak/Tanaman)', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1600', 'category' => 'biological_asset_mature'],
                ['code' => '1690', 'name' => 'Akumulasi Perubahan Nilai Wajar', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1600', 'category' => 'fair_value_gain_loss'],
                ['code' => '1350', 'name' => 'Persediaan Produk Pertanian', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000', 'category' => 'agricultural_produce'],
                
                // Revenue
                ['code' => '4300', 'name' => 'Keuntungan/Kerugian Perubahan Nilai Wajar', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '4000', 'category' => 'fair_value_gain_loss'],
            ];
            $accounts = array_merge($accounts, $psak69Accounts);
        }

        $this->seedAccounts($company, $accounts);
    }

    /**
     * Seed accounts with parent references.
     */
    protected function seedAccounts(Company $company, array $accounts): void
    {
        $codeToId = [];

        foreach ($accounts as $account) {
            $parentId = null;
            if ($account['parent'] && isset($codeToId[$account['parent']])) {
                $parentId = $codeToId[$account['parent']];
            }
            
            // Determine level from code structure
            $level = $account['parent'] ? 2 : 1;

            $created = ChartOfAccount::updateOrCreate([
                'company_id' => $company->id,
                'code' => $account['code'],
            ], [
                'name' => $account['name'],
                'type' => $account['type'],
                'report_type' => $account['report'],
                'normal_balance' => $account['normal_balance'] ?? $account['balance'],
                'is_parent' => $account['is_parent'],
                'parent_id' => $parentId,
                'level' => $level,
                'account_category' => $account['category'] ?? 'general',
                'is_system' => true,
                'is_active' => true,
            ]);

            $codeToId[$account['code']] = $created->id;
        }
    }
}
