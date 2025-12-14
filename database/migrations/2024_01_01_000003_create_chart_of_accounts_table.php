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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']);
            $table->enum('report_type', ['NERACA', 'LABARUGI']); // Posisi Keuangan atau Laba Rugi
            $table->enum('normal_balance', ['DEBIT', 'KREDIT']); // Saldo normal akun
            $table->boolean('is_parent')->default(false); // Header (tidak bisa diisi transaksi)
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System accounts cannot be deleted
            $table->integer('level')->default(1);
            $table->timestamps();
            
            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
