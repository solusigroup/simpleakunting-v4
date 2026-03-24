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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('period_type', ['MONTHLY', 'QUARTERLY', 'YEARLY'])->default('MONTHLY');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month')->nullable(); // 1-12 for monthly
            $table->unsignedTinyInteger('period_quarter')->nullable(); // 1-4 for quarterly
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'period_year']);
            $table->index(['coa_id', 'period_type']);
            $table->unique(['company_id', 'coa_id', 'business_unit_id', 'period_type', 'period_year', 'period_month', 'period_quarter'], 'budgets_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
