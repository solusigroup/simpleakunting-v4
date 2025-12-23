<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->enum('account_category', [
                'cash_bank',                // Kas & Bank
                'accounts_receivable',      // Piutang Usaha
                'other_receivable',         // Piutang Lainnya
                'inventory',                // Persediaan
                'prepaid_expense',          // Biaya Dibayar Dimuka
                'other_current_asset',      // Aset Lancar Lainnya
                'fixed_asset',              // Aset Tetap
                'accumulated_depreciation', // Akumulasi Penyusutan
                'intangible_asset',         // Aset Tidak Berwujud
                'other_asset',              // Aset Lainnya
                'accounts_payable',         // Hutang Usaha
                'other_payable',            // Hutang Lainnya
                'accrued_expense',          // Biaya Yang Masih Harus Dibayar
                'other_current_liability',  // Kewajiban Lancar Lainnya
                'long_term_liability',      // Kewajiban Jangka Panjang
                'equity_capital',           // Modal
                'equity_retained',          // Laba Ditahan
                'equity_other',             // Ekuitas Lainnya
                'revenue_sales',            // Pendapatan Penjualan
                'revenue_service',          // Pendapatan Jasa
                'revenue_other',            // Pendapatan Lainnya
                'cogs',                     // Harga Pokok Penjualan
                'expense_operational',      // Beban Operasional
                'expense_administrative',   // Beban Administrasi
                'expense_selling',          // Beban Penjualan
                'expense_other',            // Beban Lainnya
                'other_income',             // Pendapatan Lain-lain
                'other_expense',            // Beban Lain-lain
                'general',                  // Umum (tidak dikategorikan)
            ])->nullable()->after('level');
            
            // Add index for better query performance
            $table->index('account_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropIndex(['account_category']);
            $table->dropColumn('account_category');
        });
    }
};
