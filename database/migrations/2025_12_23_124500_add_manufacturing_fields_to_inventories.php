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
            if (!Schema::hasColumn('inventories', 'category')) {
                $table->enum('category', [
                    'finished_goods',    // Barang Jadi/Barang Dagangan
                    'raw_materials',     // Bahan Baku
                    'work_in_process',   // Barang Dalam Proses (WIP)
                    'supplies',          // Bahan Pembantu/Supplies
                ])->default('finished_goods')->after('name');
            }
            
            // Add assembly flag
            if (!Schema::hasColumn('inventories', 'is_assembly')) {
                $table->boolean('is_assembly')->default(false)->after('category')
                    ->comment('True if this item can be assembled from components');
            }
            
            // Add description field
            if (!Schema::hasColumn('inventories', 'description')) {
                $table->text('description')->nullable()->after('is_assembly');
            }
        });
        
        // Add index for category (separate call to avoid issues)
        if (Schema::hasColumn('inventories', 'category')) {
            try {
                Schema::table('inventories', function (Blueprint $table) {
                    $table->index('category');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
        }
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
