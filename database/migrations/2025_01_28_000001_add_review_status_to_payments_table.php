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
            $table->string('review_status')->default('pending')->after('note')
                ->comment('pending, reviewed, confirmed, disputed');
            $table->timestamp('reviewed_at')->nullable()->after('review_status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->text('review_note')->nullable()->after('reviewed_by');
            
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('review_status');
            $table->index('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['review_status']);
            $table->dropIndex(['reviewed_at']);
            
            $table->dropColumn([
                'review_status',
                'reviewed_at',
                'reviewed_by',
                'review_note',
            ]);
        });
    }
};
