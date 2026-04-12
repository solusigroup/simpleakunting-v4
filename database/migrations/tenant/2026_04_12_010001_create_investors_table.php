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
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('share_percentage', 5, 2);
            $table->enum('basis', ['GROSS_PROFIT', 'NET_PROFIT']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('investor_sharings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('basis_amount', 15, 2); // The profit amount used
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_sharings');
        Schema::dropIfExists('investors');
    }
};
