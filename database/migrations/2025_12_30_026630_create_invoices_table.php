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
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('type', ['DEPOSIT', 'RENT', 'FEE', 'OTHER'])->default('RENT');
            $table->enum('status', ['UNPAID', 'PAID', 'OVERDUE', 'CANCELLED'])->default('UNPAID');
            $table->timestamp('generated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
