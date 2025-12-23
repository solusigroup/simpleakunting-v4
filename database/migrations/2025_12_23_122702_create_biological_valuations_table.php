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
        Schema::create('biological_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biological_asset_id')->constrained()->onDelete('cascade');
            
            $table->date('valuation_date');
            
            // Fair Value Changes
            $table->decimal('previous_fair_value', 15, 2);
            $table->decimal('current_fair_value', 15, 2);
            $table->decimal('cost_to_sell', 15, 2)->default(0);
            $table->decimal('fair_value_change', 15, 2)->comment('Perubahan nilai wajar (current - previous)');
            
            // Valuation Method & Notes
            $table->string('valuation_method', 100)->nullable()->comment('Market price, DCF, Independent appraisal, dll');
            $table->text('valuation_notes')->nullable();
            
            // Link to journal entry for fair value adjustment
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            
            // Auditing
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['biological_asset_id', 'valuation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biological_valuations');
    }
};
