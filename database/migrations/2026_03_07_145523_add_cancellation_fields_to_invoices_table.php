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
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('cancelled_reason')->nullable()->after('notes');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
