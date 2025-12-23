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
        Schema::table('inventories', function (Blueprint $table) {
            // Add inventory category for manufacturing
            $table->enum('category', [
                'finished_goods',    // Barang Jadi/Barang Dagangan
                'raw_materials',     // Bahan Baku
                'work_in_process',   // Barang Dalam Proses (WIP)
                'supplies',          // Bahan Pembantu/Supplies
            ])->default('finished_goods')->after('name');
            
            // Add assembly flag
            $table->boolean('is_assembly')->default(false)->after('category')
                ->comment('True if this item can be assembled from components');
            
            // Add description field
            $table->text('description')->nullable()->after('is_assembly');
            
            // Add index for category
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'is_assembly', 'description']);
        });
    }
};
