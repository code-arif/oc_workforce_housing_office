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
        Schema::table('leases', function (Blueprint $table) {
            $table->boolean('deposit_collected')->default(false)->after('deposit_amount');
            $table->boolean('send_for_signature')->default(false)->after('deposit_collected');
            $table->boolean('send_welcome_email')->default(false)->after('send_for_signature');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('deposit_collected');
            $table->dropColumn('send_for_signature');
            $table->dropColumn('send_welcome_email');
        });
    }
};
