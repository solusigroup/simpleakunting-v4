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
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        // Add more enum values to the source column
        // Note: For MySQL, we need to manually alter the column
        DB::statement("ALTER TABLE journals MODIFY COLUMN source ENUM('manual', 'sales', 'purchase', 'cash_bank', 'adjustment', 'internet_billing', 'internet_payment') DEFAULT 'manual'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE journals MODIFY COLUMN source ENUM('manual', 'sales', 'purchase', 'cash_bank', 'adjustment') DEFAULT 'manual'");
    }
};
