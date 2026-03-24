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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('restrict');
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('business_unit_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['Sales', 'Purchase']);
            $table->string('invoice_number');
            $table->date('date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('status', ['Draft', 'Posted', 'Paid'])->default('Draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'date', 'type']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('inventory_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
