<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number');
            $table->decimal('amount', 10, 2); // Base rent amount
            $table->decimal('total_amount', 10, 2); // Total including deposit if applicable
            $table->decimal('paid_amount', 10, 2)->default(0.00); // Track total paid
            $table->decimal('balance_due', 10, 2); // Remaining balance
            $table->date('issue_date')->nullable();
            $table->date('due_date');
            $table->enum('type', ['DEPOSIT', 'RENT', 'FEE', 'OTHER'])->default('RENT');
            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED'])->default('UNPAID');
            $table->boolean('is_first_invoice')->default(false); // Track first invoice for deposit
            $table->boolean('includes_deposit')->default(false); // Whether this invoice includes deposit
            $table->timestamp('paid_at')->nullable(); // When fully paid
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For additional data
            $table->softDeletes();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['tenant_id', 'status']);
            $table->index(['lease_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
