<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table
        // For MySQL/PostgreSQL, we can use ALTER
        
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: Drop index, then column, then recreate
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->dropIndex(['account_category']);
            });
            
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->dropColumn('account_category');
            });
            
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->string('account_category', 50)->nullable()->after('level');
                $table->index('account_category');
            });
        } else {
            // MySQL/PostgreSQL: Modify enum
            DB::statement("ALTER TABLE chart_of_accounts MODIFY COLUMN account_category ENUM(
                'cash_bank',
                'accounts_receivable',
                'other_receivable',
                'inventory',
                'prepaid_expense',
                'other_current_asset',
                'fixed_asset',
                'accumulated_depreciation',
                'intangible_asset',
                'other_asset',
                'biological_asset',
                'biological_asset_immature',
                'biological_asset_mature',
                'agricultural_produce',
                'fair_value_gain_loss',
                'accounts_payable',
                'other_payable',
                'accrued_expense',
                'other_current_liability',
                'long_term_liability',
                'equity_capital',
                'equity_retained',
                'equity_other',
                'revenue_sales',
                'revenue_service',
                'revenue_other',
                'cogs',
                'expense_operational',
                'expense_administrative',
                'expense_selling',
                'expense_other',
                'other_income',
                'other_expense',
                'general'
            ) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite: Just keep the column as string
            // Data won't be lost
        } else {
            // MySQL/PostgreSQL: Revert to original enum
            DB::statement("ALTER TABLE chart_of_accounts MODIFY COLUMN account_category ENUM(
                'cash_bank',
                'accounts_receivable',
                'other_receivable',
                'inventory',
                'prepaid_expense',
                'other_current_asset',
                'fixed_asset',
                'accumulated_depreciation',
                'intangible_asset',
                'other_asset',
                'accounts_payable',
                'other_payable',
                'accrued_expense',
                'other_current_liability',
                'long_term_liability',
                'equity_capital',
                'equity_retained',
                'equity_other',
                'revenue_sales',
                'revenue_service',
                'revenue_other',
                'cogs',
                'expense_operational',
                'expense_administrative',
                'expense_selling',
                'expense_other',
                'other_income',
                'other_expense',
                'general'
            ) NULL");
        }
    }
};
