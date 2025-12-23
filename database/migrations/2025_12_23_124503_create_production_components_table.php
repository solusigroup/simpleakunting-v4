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
        Schema::create('production_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->onDelete('cascade');
            $table->foreignId('component_id')->constrained('inventories')->onDelete('restrict')
                ->comment('ID komponen/bahan baku yang digunakan');
            $table->decimal('quantity_required', 10, 2)
                ->comment('Jumlah yang dibutuhkan sesuai BOM');
            $table->decimal('quantity_used', 10, 2)
                ->comment('Jumlah yang benar-benar digunakan');
            $table->string('unit', 50);
            $table->decimal('unit_cost', 15, 2)
                ->comment('Biaya per unit komponen');
            $table->decimal('total_cost', 15, 2)
                ->comment('Total biaya komponen (quantity_used * unit_cost)');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('production_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_components');
    }
};
