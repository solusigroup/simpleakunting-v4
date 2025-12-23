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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('production_number', 50)->comment('Nomor produksi');
            $table->date('production_date');
            $table->foreignId('assembly_id')->constrained('inventories')->onDelete('restrict')
                ->comment('ID barang jadi yang diproduksi');
            $table->decimal('quantity', 10, 2)->comment('Jumlah barang jadi yang diproduksi');
            $table->string('unit', 50);
            $table->decimal('total_material_cost', 15, 2)->default(0)
                ->comment('Total biaya bahan baku');
            $table->decimal('labor_cost', 15, 2)->default(0)
                ->comment('Biaya tenaga kerja');
            $table->decimal('overhead_cost', 15, 2)->default(0)
                ->comment('Biaya overhead pabrik');
            $table->decimal('total_cost', 15, 2)->default(0)
                ->comment('Total biaya produksi');
            $table->decimal('unit_cost', 15, 2)->default(0)
                ->comment('Biaya per unit');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->unique(['company_id', 'production_number']);
            $table->index(['production_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
