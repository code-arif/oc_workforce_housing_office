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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('base_amount', 10, 2)->nullable()->after('amount')->comment('Amount credited to the invoice');
            $table->decimal('processing_fee', 10, 2)->default(0)->after('base_amount')->comment('Stripe/Gateway processing fee');
            $table->decimal('total_charged', 10, 2)->nullable()->after('processing_fee')->comment('base_amount + processing_fee');
            $table->string('stripe_payment_intent_id')->nullable()->after('gateway_transaction_id');
            $table->date('deposit_date')->nullable()->after('payment_date')->comment('Date funds were deposited to bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'base_amount',
                'processing_fee',
                'total_charged',
                'stripe_payment_intent_id',
                'deposit_date'
            ]);
        });
    }
};
