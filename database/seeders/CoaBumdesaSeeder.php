<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CoaBumdesaSeeder extends Seeder
{
    /**
     * Seed Chart of Accounts for BUMDesa (Kepmendesa 136/2022).
     * Schema A: Kode format 1.1.1
     */
    public function run(Company $company): void
    {
        $accounts = [
            // ASET (ASSETS)
            ['code' => '1', 'name' => 'ASET', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => true],
            ['code' => '1.1', 'name' => 'Aset Lancar', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => true],
            ['code' => '1.1.1', 'name' => 'Kas dan Setara Kas', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '1.1.2', 'name' => 'Piutang Usaha', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '1.1.3', 'name' => 'Piutang Non Usaha', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '1.1.4', 'name' => 'Persediaan', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '1.1.5', 'name' => 'Biaya Dibayar Dimuka', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            
            ['code' => '1.2', 'name' => 'Aset Tidak Lancar', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => true],
            ['code' => '1.2.1', 'name' => 'Aset Tetap (Tanah, Bangunan, Peralatan)', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '1.2.2', 'name' => 'Akumulasi Penyusutan', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '1.2.3', 'name' => 'Aset Kerjasama / Investasi', 'type' => 'Asset', 'report' => 'NERACA', 'balance' => 'DEBIT', 'is_parent' => false],
            
            // KEWAJIBAN (LIABILITIES)
            ['code' => '2', 'name' => 'KEWAJIBAN', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true],
            ['code' => '2.1', 'name' => 'Kewajiban Jangka Pendek', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true],
            ['code' => '2.1.1', 'name' => 'Utang Usaha', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '2.1.2', 'name' => 'Utang Pajak', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '2.1.3', 'name' => 'Pendapatan Diterima Dimuka', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '2.2', 'name' => 'Kewajiban Jangka Panjang', 'type' => 'Liability', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            
            // EKUITAS (EQUITY) - Sesuai PP 11/2021 & Kepmendesa 136
            ['code' => '3', 'name' => 'EKUITAS', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => true],
            ['code' => '3.1', 'name' => 'Penyertaan Modal Desa', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '3.2', 'name' => 'Penyertaan Modal Masyarakat', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '3.3', 'name' => 'Cadangan / Laba Ditahan', 'type' => 'Equity', 'report' => 'NERACA', 'balance' => 'KREDIT', 'is_parent' => false],
            
            // PENDAPATAN (REVENUE)
            ['code' => '4', 'name' => 'PENDAPATAN', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => true],
            ['code' => '4.1', 'name' => 'Pendapatan Usaha', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => false],
            ['code' => '4.2', 'name' => 'Pendapatan Lain-lain', 'type' => 'Revenue', 'report' => 'LABARUGI', 'balance' => 'KREDIT', 'is_parent' => false],
            
            // BEBAN (EXPENSES)
            ['code' => '5', 'name' => 'BEBAN', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => true],
            ['code' => '5.1', 'name' => 'Beban Pokok Pendapatan (HPP)', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '5.2', 'name' => 'Beban Operasional (Gaji, ATK, Listrik)', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false],
            ['code' => '5.3', 'name' => 'Beban Pajak', 'type' => 'Expense', 'report' => 'LABARUGI', 'balance' => 'DEBIT', 'is_parent' => false],
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
            // Determine parent from code (e.g., 1.1.1 -> parent is 1.1)
            $parentCode = $this->getParentCode($account['code']);
            $parentId = $parentCode ? ($codeToId[$parentCode] ?? null) : null;
            
            // Determine level from code
            $level = substr_count($account['code'], '.') + 1;

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

    /**
     * Get parent code from child code (e.g., 1.1.1 -> 1.1)
     */
    protected function getParentCode(string $code): ?string
    {
        $parts = explode('.', $code);
        if (count($parts) <= 1) {
            return null;
        }
        array_pop($parts);
        return implode('.', $parts);
    }
}
