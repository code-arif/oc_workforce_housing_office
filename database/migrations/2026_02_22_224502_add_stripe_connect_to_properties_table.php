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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('is_active');
            $table->enum('stripe_account_status', ['not_connected', 'pending', 'active', 'restricted', 'disabled'])
                ->default('not_connected')->after('stripe_account_id');
            $table->boolean('stripe_onboarding_completed')->default(false)->after('stripe_account_status');
            $table->json('stripe_account_data')->nullable()->after('stripe_onboarding_completed');
            $table->timestamp('stripe_connected_at')->nullable()->after('stripe_account_data');

            $table->index('stripe_account_id');
            $table->index('stripe_account_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_account_id',
                'stripe_account_status',
                'stripe_onboarding_completed',
                'stripe_account_data',
                'stripe_connected_at',
            ]);
        });
    }
};
