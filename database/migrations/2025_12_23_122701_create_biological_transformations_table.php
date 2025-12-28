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
        if (Schema::hasTable('biological_transformations')) {
            return;
        }
        
        Schema::create('biological_transformations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biological_asset_id')->constrained()->onDelete('cascade');
            
            $table->enum('transformation_type', [
                'growth',           // Pertumbuhan (berat bertambah, pohon tumbuh)
                'degeneration',     // Degenerasi (penuaan, penyusutan)
                'production',       // Produksi (bertelur, berbuah tanpa panen)
                'procreation',      // Prokreasi (berkembang biak, anak lahir)
                'death',            // Kematian/kehilangan
                'harvest'           // Panen
            ]);
            
            $table->date('transaction_date');
            $table->decimal('quantity_change', 10, 2)->comment('Positif untuk penambahan, negatif untuk pengurangan');
            $table->text('description')->nullable();
            
            // Link to journal if transaction creates accounting entry
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['biological_asset_id', 'transaction_date']);
            $table->index('transformation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biological_transformations');
    }
};
