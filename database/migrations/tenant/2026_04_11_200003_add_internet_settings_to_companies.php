<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('internet_receivable_module_coa_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('internet_revenue_module_coa_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['internet_receivable_module_coa_id']);
            $table->dropForeign(['internet_revenue_module_coa_id']);
            $table->dropColumn(['internet_receivable_module_coa_id', 'internet_revenue_module_coa_id']);
        });
    }
};
