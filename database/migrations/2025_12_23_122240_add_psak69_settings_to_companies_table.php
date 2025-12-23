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
            // PSAK 69 Configuration
            $table->boolean('enable_psak69')->default(false)->after('fiscal_start');
            $table->enum('business_sector', [
                'general',          // Umum (non-agrikultur)
                'livestock',        // Peternakan
                'plantation',       // Perkebunan
                'aquaculture',      // Perikanan/Budidaya
                'forestry',         // Kehutanan
                'mixed_agriculture' // Agrikultur Campuran
            ])->default('general')->after('enable_psak69');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['enable_psak69', 'business_sector']);
        });
    }
};
