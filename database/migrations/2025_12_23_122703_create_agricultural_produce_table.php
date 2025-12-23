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
        Schema::create('agricultural_produce', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('biological_asset_id')->constrained()->onDelete('cascade');
            
            $table->date('harvest_date');
            $table->string('product_name');
            
            // Quantity
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 50);
            
            // Fair Value at Harvest (PSAK 69 requirement)
            $table->decimal('fair_value_at_harvest', 15, 2);
            $table->decimal('cost_to_sell', 15, 2)->default(0);
            $table->decimal('carrying_amount', 15, 2)->comment('Fair value - Cost to sell');
            
            // Link to inventory after harvest
            $table->foreignId('inventory_id')->nullable()->constrained()->onDelete('set null');
            
            // Chart of Account for produce/inventory
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->comment('Akun Inventory/Produk Agrikultur');
            
            // Link to journal entry
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'harvest_date']);
            $table->index('biological_asset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agricultural_produce');
    }
};
