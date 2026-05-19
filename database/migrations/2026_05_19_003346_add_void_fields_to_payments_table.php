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
            $table->string('status')->default('active')->after('review_status'); // active, voided
            $table->text('void_reason')->nullable()->after('note');
            $table->unsignedBigInteger('voided_by')->nullable()->after('recorded_by');
            $table->timestamp('voided_at')->nullable()->after('void_reason');

            $table->foreign('voided_by')->references('id')->on('users')->onDelete('set null');

            $table->index('status');
            $table->index('voided_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['voided_at']);

            $table->dropColumn([
                'status',
                'void_reason',
                'voided_by',
                'voided_at',
            ]);
        });
    }
};
