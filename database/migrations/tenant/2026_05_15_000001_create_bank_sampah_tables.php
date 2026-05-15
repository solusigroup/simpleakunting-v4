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
        Schema::create('waste_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('kg');
            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('waste_collectors', function (Blueprint $table) {
            $table->id();
            $table->string('collector_number')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('waste_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_number')->unique();
            $table->foreignId('waste_collector_id')->constrained('waste_collectors');
            $table->foreignId('waste_category_id')->constrained('waste_categories');
            $table->decimal('weight', 10, 2);
            $table->decimal('price_at_time', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->date('date');
            $table->foreignId('journal_id')->nullable()->constrained('journals');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('waste_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('waste_category_id')->constrained('waste_categories');
            $table->decimal('weight', 10, 2);
            $table->decimal('price_at_time', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->date('date');
            $table->string('buyer_name')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('waste_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('withdrawal_number')->unique();
            $table->foreignId('waste_collector_id')->constrained('waste_collectors');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->foreignId('journal_id')->nullable()->constrained('journals');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_withdrawals');
        Schema::dropIfExists('waste_sales');
        Schema::dropIfExists('waste_deposits');
        Schema::dropIfExists('waste_collectors');
        Schema::dropIfExists('waste_categories');
    }
};
