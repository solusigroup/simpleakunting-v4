<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internet_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('customer_id', 20)->unique()->comment('PLG-001');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('package_name')->comment('Nama paket: 10 Mbps, 20 Mbps, etc');
            $table->decimal('monthly_rate', 15, 2)->comment('Tarif bulanan');
            $table->unsignedTinyInteger('billing_date')->default(1)->comment('Tanggal tagih (1-28)');
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->date('activated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internet_customers');
    }
};
