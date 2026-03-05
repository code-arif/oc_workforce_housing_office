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
        Schema::table('tenants', function (Blueprint $table) {
                $table->unsignedBigInteger('application_id')->nullable()->after('id');
                $table->foreign('application_id')->references('id')->on('applications')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
                $table->dropColumn('application_id');
        });
    }
};
