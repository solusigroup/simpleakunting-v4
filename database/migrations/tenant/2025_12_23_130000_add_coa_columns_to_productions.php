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
        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('labor_coa_id')->nullable()->after('labor_cost')->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('overhead_coa_id')->nullable()->after('overhead_cost')->constrained('chart_of_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['labor_coa_id']);
            $table->dropForeign(['overhead_coa_id']);
            $table->dropColumn(['labor_coa_id', 'overhead_coa_id']);
        });
    }
};
