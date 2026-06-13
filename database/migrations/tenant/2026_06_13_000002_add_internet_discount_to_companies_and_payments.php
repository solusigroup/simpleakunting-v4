<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('internet_discount_coa_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->nullOnDelete();
        });

        Schema::table('internet_payments', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('internet_payments', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['internet_discount_coa_id']);
            $table->dropColumn('internet_discount_coa_id');
        });
    }
};
