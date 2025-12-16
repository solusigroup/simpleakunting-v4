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
            $table->string('director_name')->nullable()->after('fiscal_start');
            $table->string('director_title')->nullable()->after('director_name');
            $table->string('secretary_name')->nullable()->after('director_title');
            $table->string('secretary_title')->nullable()->after('secretary_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['director_name', 'director_title', 'secretary_name', 'secretary_title']);
        });
    }
};
