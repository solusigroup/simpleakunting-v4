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
            ['code' => '1000', 'name' => 'ASET', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => null],
            ['code' => '1100', 'name' => 'Kas & Bank', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000'],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000'],
            ['code' => '1300', 'name' => 'Persediaan Barang', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000'],
            ['code' => '1500', 'name' => 'Aset Tetap', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '1000'],
            ['code' => '1599', 'name' => 'Akumulasi Penyusutan', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '1000'],
            
            // LIABILITIES (2000-2999)
            ['code' => '2000', 'name' => 'KEWAJIBAN', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true, 'parent' => null],
            ['code' => '2100', 'name' => 'Utang Usaha', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '2000'],
            ['code' => '2200', 'name' => 'Utang Bank', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '2000'],
            
            // EQUITY (3000-3999)
            ['code' => '3000', 'name' => 'EKUITAS', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true, 'parent' => null],
            ['code' => '3100', 'name' => 'Modal Pemilik', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '3000'],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '3000'],
            ['code' => '3300', 'name' => 'Prive (Penarikan Modal)', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '3000'],
            
            // REVENUE (4000-4999)
            ['code' => '4000', 'name' => 'PENDAPATAN', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => true, 'parent' => null],
            ['code' => '4100', 'name' => 'Penjualan Barang/Jasa', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => false, 'parent' => '4000'],
            ['code' => '4200', 'name' => 'Potongan Penjualan', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '4000'],
            
            // COGS / HPP (5000-5999)
            ['code' => '5000', 'name' => 'HARGA POKOK PENJUALAN', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => null],
            ['code' => '5100', 'name' => 'Beban Pokok Pendapatan', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '5000'],
            
            // EXPENSES (6000-6999)
            ['code' => '6000', 'name' => 'BEBAN OPERASIONAL', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => true, 'parent' => null],
            ['code' => '6100', 'name' => 'Gaji & Upah', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000'],
            ['code' => '6200', 'name' => 'Sewa Bangunan', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000'],
            ['code' => '6300', 'name' => 'Listrik, Air & Telepon', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000'],
            ['code' => '6400', 'name' => 'Perlengkapan (ATK)', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false, 'parent' => '6000'],
        ];

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

            $created = ChartOfAccount::create([
                'company_id' => $company->id,
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'report_type' => $account['report'],
                'normal_balance' => $account['balance'],
                'is_parent' => $account['is_parent'],
                'parent_id' => $parentId,
                'level' => $level,
                'is_system' => true,
                'is_active' => true,
            ]);

            $codeToId[$account['code']] = $created->id;
        }
    }
}
