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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('stripe_key')->nullable();
            $table->string('stripe_secret')->nullable();
            $table->string('stripe_webhook_secret')->nullable();
            $table->decimal('stripe_ach_fee', 8, 2)->nullable();
            $table->decimal('stripe_card_fee_percentage', 8, 2)->nullable();
            $table->decimal('stripe_card_fee_fixed', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_key',
                'stripe_secret',
                'stripe_webhook_secret',
                'stripe_ach_fee',
                'stripe_card_fee_percentage',
                'stripe_card_fee_fixed',
            ]);
        });
    }
};
