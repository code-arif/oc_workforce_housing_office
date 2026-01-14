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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique(); // e.g., PAY-00001
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'credit_card', 'debit_card', 'online', 'stripe', 'paypal', 'other'])->default('cash');
            $table->string('reference_number')->nullable(); // Check number, transaction ID, etc.
            $table->string('gateway_transaction_id')->nullable(); // For online payments
            $table->enum('payment_type', ['rent', 'deposit', 'partial', 'full'])->default('partial');
            $table->enum('paid_by', ['tenant', 'admin', 'system'])->default('tenant');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); // Who recorded this payment
            $table->text('note')->nullable();
            $table->json('metadata')->nullable(); // For gateway response, etc.
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'payment_date']);
            $table->index(['tenant_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
