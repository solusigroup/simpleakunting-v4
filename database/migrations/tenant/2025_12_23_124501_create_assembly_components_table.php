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
        Schema::create('assembly_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('assembly_id')->constrained('inventories')->onDelete('cascade')
                ->comment('ID barang jadi yang akan dirakit');
            $table->foreignId('component_id')->constrained('inventories')->onDelete('cascade')
                ->comment('ID komponen/bahan baku');
            $table->decimal('quantity', 10, 2)->comment('Jumlah komponen yang dibutuhkan');
            $table->string('unit', 50)->comment('Satuan komponen');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['assembly_id', 'component_id']);
            $table->unique(['assembly_id', 'component_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_components');
    }
};
