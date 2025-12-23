<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use Illuminate\Console\Command;

class AutoCategorizeCOA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:auto-categorize 
                            {--company= : Specific company ID to categorize}
                            {--dry-run : Show what would be changed without actually changing}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically categorize Chart of Accounts based on code patterns and names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->option('company');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Auto-Categorizing Chart of Accounts...');
        $this->newLine();

        // Get accounts without category
        $query = ChartOfAccount::whereNull('account_category');
        
        if ($companyId) {
            $query->where('company_id', $companyId);
            $this->info("Filtering for company ID: {$companyId}");
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->info('✅ No accounts need categorization!');
            return 0;
        }

        $this->info("Found {$accounts->count()} accounts without category");
        $this->newLine();

        if (!$force && !$dryRun) {
            if (!$this->confirm('Do you want to continue?', true)) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        $categorized = 0;
        $skipped = 0;

        foreach ($accounts as $account) {
            $category = $this->detectCategory($account);

            if ($category) {
                $categorized++;
                
                if ($dryRun) {
                    $this->line("  [DRY-RUN] {$account->code} - {$account->name} → {$category}");
                } else {
                    $account->update(['account_category' => $category]);
                    $this->line("  ✓ {$account->code} - {$account->name} → {$category}");
                }
            } else {
                $skipped++;
                $this->line("  ⊘ {$account->code} - {$account->name} (no match)");
            }
        }

        $this->newLine();
        
        if ($dryRun) {
            $this->info("📊 DRY-RUN Results:");
        } else {
            $this->info("📊 Results:");
        }
        
        $this->table(
            ['Status', 'Count'],
            [
                ['Categorized', $categorized],
                ['Skipped', $skipped],
                ['Total', $accounts->count()],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->info('💡 Run without --dry-run to apply changes');
        }

        return 0;
    }

    /**
     * Detect category based on code and name patterns.
     */
    protected function detectCategory(ChartOfAccount $account): ?string
    {
        $code = $account->code;
        $name = strtolower($account->name);
        $type = $account->type;

        // Priority 1: Check for specific keywords that should override general patterns
        
        // Accumulated Depreciation (check first before fixed asset)
        if (str_contains($name, 'akumulasi') ||
            str_contains($name, 'accumulated') ||
            str_contains($name, 'penyusutan') ||
            str_contains($name, 'depreciation')) {
            return 'accumulated_depreciation';
        }

        // Accounts Payable (check before general bank/cash)
        if (str_contains($name, 'hutang') || str_contains($name, 'utang')) {
            if (str_contains($name, 'usaha') || str_contains($name, 'dagang')) {
                return 'accounts_payable';
            }
            if (str_contains($name, 'bank') || str_contains($name, 'jangka panjang')) {
                return 'long_term_liability';
            }
            return 'other_payable';
        }

        // Priority 2: Code-based detection (most reliable)
        
        // Cash & Bank
        if (preg_match('/^1\.1\.1|^1100|^111/', $code)) {
            return 'cash_bank';
        }

        // Accounts Receivable
        if (preg_match('/^1\.1\.2|^1120|^112/', $code)) {
            return 'accounts_receivable';
        }

        // Other Receivable
        if (preg_match('/^1\.1\.3|^1130|^113/', $code)) {
            return 'other_receivable';
        }

        // Inventory
        if (preg_match('/^1\.1\.4|^114/', $code)) {
            return 'inventory';
        }

        // Prepaid Expense
        if (preg_match('/^1\.1\.5|^115/', $code)) {
            return 'prepaid_expense';
        }

        // Fixed Asset
        if (preg_match('/^1\.2|^12[0-9]/', $code)) {
            return 'fixed_asset';
        }

        // Accounts Payable
        if (preg_match('/^2\.1\.1|^2110|^211/', $code)) {
            return 'accounts_payable';
        }

        // Other Current Liability
        if (preg_match('/^2\.1|^21[0-9]/', $code)) {
            return 'other_current_liability';
        }

        // Long-term Liability
        if (preg_match('/^2\.2|^22[0-9]/', $code)) {
            return 'long_term_liability';
        }

        // Equity - Capital
        if (preg_match('/^3\.1|^31[0-9]/', $code)) {
            return 'equity_capital';
        }

        // Equity - Retained Earnings
        if (preg_match('/^3\.2|^32[0-9]/', $code)) {
            return 'equity_retained';
        }

        // Revenue - Sales
        if (preg_match('/^4\.1|^41[0-9]/', $code)) {
            return 'revenue_sales';
        }

        // Revenue - Service
        if (preg_match('/^4\.2|^42[0-9]/', $code)) {
            return 'revenue_service';
        }

        // COGS
        if (preg_match('/^5\.1|^5100|^51[0-9]/', $code)) {
            return 'cogs';
        }

        // Expense - Operational
        if (preg_match('/^6\.1|^6100|^61[0-9]/', $code)) {
            return 'expense_operational';
        }

        // Expense - Administrative
        if (preg_match('/^6\.2|^6200|^62[0-9]/', $code)) {
            return 'expense_administrative';
        }

        // Expense - Selling
        if (preg_match('/^6\.3|^6300|^63[0-9]/', $code)) {
            return 'expense_selling';
        }

        // Priority 3: Name-based detection (less reliable but still useful)
        
        // Cash & Bank (only if type is Asset)
        if ($type === 'Asset' && (str_contains($name, 'kas') || str_contains($name, 'bank') || str_contains($name, 'cash'))) {
            return 'cash_bank';
        }

        // Piutang
        if (str_contains($name, 'piutang')) {
            if (str_contains($name, 'usaha') || str_contains($name, 'dagang')) {
                return 'accounts_receivable';
            }
            return 'other_receivable';
        }

        // Inventory
        if (str_contains($name, 'persediaan') ||
            str_contains($name, 'inventory') ||
            str_contains($name, 'stock')) {
            return 'inventory';
        }

        // Prepaid Expense
        if (str_contains($name, 'dibayar dimuka') ||
            str_contains($name, 'prepaid')) {
            return 'prepaid_expense';
        }

        // Fixed Asset (exclude expenses like "Sewa")
        if ($type === 'Asset' && 
            ((str_contains($name, 'aset tetap') && !str_contains($name, 'akumulasi')) ||
            (str_contains($name, 'fixed asset') && !str_contains($name, 'accumulated')) ||
            str_contains($name, 'peralatan') ||
            str_contains($name, 'kendaraan') ||
            str_contains($name, 'gedung') ||
            str_contains($name, 'bangunan') ||
            str_contains($name, 'mesin'))) {
            return 'fixed_asset';
        }

        // Equity - Retained Earnings
        if (str_contains($name, 'laba ditahan') ||
            str_contains($name, 'retained') ||
            str_contains($name, 'saldo laba')) {
            return 'equity_retained';
        }

        // Equity - Capital
        if ($type === 'Equity' && (str_contains($name, 'modal') || str_contains($name, 'capital') || str_contains($name, 'prive'))) {
            return 'equity_capital';
        }

        // Revenue - Sales
        if ($type === 'Revenue' && (str_contains($name, 'penjualan') || str_contains($name, 'sales'))) {
            return 'revenue_sales';
        }

        // Revenue - Service
        if ($type === 'Revenue' && (str_contains($name, 'jasa') || str_contains($name, 'service'))) {
            return 'revenue_service';
        }

        // COGS
        if (str_contains($name, 'harga pokok') ||
            str_contains($name, 'hpp') ||
            str_contains($name, 'cogs') ||
            str_contains($name, 'cost of goods') ||
            str_contains($name, 'beban pokok')) {
            return 'cogs';
        }

        // Expense - Operational
        if ($type === 'Expense' && str_contains($name, 'operasional')) {
            return 'expense_operational';
        }

        // Expense - Administrative
        if ($type === 'Expense' && (str_contains($name, 'administrasi') || str_contains($name, 'administrative'))) {
            return 'expense_administrative';
        }

        // Priority 4: Fallback based on type and general code patterns
        
        // Other Current Asset
        if (preg_match('/^1\.1|^11[0-9]/', $code) && $type === 'Asset') {
            return 'other_current_asset';
        }

        // Other Asset
        if (preg_match('/^1\.|^1[0-9]/', $code) && $type === 'Asset') {
            return 'other_asset';
        }

        // Other Current Liability
        if (preg_match('/^2\.1|^21[0-9]/', $code) && $type === 'Liability') {
            return 'other_current_liability';
        }

        // Long-term Liability
        if (preg_match('/^2\.2|^22[0-9]/', $code) && $type === 'Liability') {
            return 'long_term_liability';
        }

        // Equity - Other
        if ($type === 'Equity') {
            return 'equity_other';
        }

        // Revenue - Other
        if ($type === 'Revenue') {
            return 'revenue_other';
        }

        // Expense - Other
        if ($type === 'Expense') {
            return 'expense_other';
        }

        return null;
    }
}
