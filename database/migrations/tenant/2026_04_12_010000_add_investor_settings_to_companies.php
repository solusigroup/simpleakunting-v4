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
            $table->foreignId('investor_sharing_debit_coa_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('investor_sharing_credit_coa_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['investor_sharing_debit_coa_id']);
            $table->dropForeign(['investor_sharing_credit_coa_id']);
            $table->dropColumn(['investor_sharing_debit_coa_id', 'investor_sharing_credit_coa_id']);
        });
    }
};
