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
        Schema::create('biological_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code', 50);
            $table->string('name');
            
            // Classification
            $table->enum('category', [
                'livestock',        // Peternakan (sapi, kambing, ayam, dll)
                'plantation',       // Perkebunan (kelapa sawit, karet, kopi, dll)
                'aquaculture',      // Perikanan/Budidaya (ikan, udang, dll)
                'forestry',         // Kehutanan (kayu, bambu, dll)
                'other'             // Lainnya
            ]);
            
            $table->enum('asset_type', [
                'consumable',       // Habis pakai (dijual/dipanen)
                'bearer'            // Penghasil (menghasilkan produk berulang)
            ]);
            
            $table->enum('maturity_status', [
                'immature',         // Belum dewasa/produktif
                'mature'            // Sudah dewasa/produktif
            ])->default('immature');
            
            // Quantity & Unit
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50); // ekor, pohon, kg, ton, dll
            
            // Acquisition
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            
            // Fair Value Measurement
            $table->decimal('current_fair_value', 15, 2)->nullable();
            $table->decimal('cost_to_sell', 15, 2)->default(0);
            $table->decimal('carrying_amount', 15, 2); // Fair value - Cost to sell
            
            $table->enum('valuation_method', [
                'fair_value',       // Nilai wajar
                'cost_model'        // Biaya perolehan (jika nilai wajar tidak dapat diukur)
            ])->default('fair_value');
            
            $table->date('valuation_date')->nullable();
            
            // Location & Notes
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            
            // Chart of Accounts
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->comment('Akun Aset Biologis');
            $table->foreignId('fair_value_gain_loss_coa_id')->nullable()->constrained('chart_of_accounts')->comment('Akun Keuntungan/Kerugian Nilai Wajar');
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biological_assets');
    }
};
