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
        Schema::table('team_users', function (Blueprint $table) {
            $table->boolean('is_leader')->default(false)->after('user_id');
            $table->boolean('is_tracking_active')->default(true)->after('is_leader');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_users', function (Blueprint $table) {
            $table->dropColumn(['is_leader', 'is_tracking_active']);
        });
    }
};
