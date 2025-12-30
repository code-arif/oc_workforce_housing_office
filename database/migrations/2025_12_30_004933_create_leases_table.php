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
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->nullable();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['DRAFT', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN', 'ACTIVE', 'TERMINATED', 'COMPLETED'])->default('DRAFT');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0.00);
            $table->enum('payment_frequency', ['WEEKLY', 'BIWEEKLY', 'MONTHLY', 'CUSTOM']);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
