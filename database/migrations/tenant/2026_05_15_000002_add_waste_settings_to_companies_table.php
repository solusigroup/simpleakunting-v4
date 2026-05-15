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
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('waste_inventory_account_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('waste_liability_account_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('waste_revenue_account_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('waste_cash_account_id')->nullable()->constrained('chart_of_accounts'); // Default cash account for withdrawals
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['waste_inventory_account_id']);
            $table->dropForeign(['waste_liability_account_id']);
            $table->dropForeign(['waste_revenue_account_id']);
            $table->dropForeign(['waste_cash_account_id']);
            $table->dropColumn([
                'waste_inventory_account_id',
                'waste_liability_account_id',
                'waste_revenue_account_id',
                'waste_cash_account_id'
            ]);
        });
    }
};
