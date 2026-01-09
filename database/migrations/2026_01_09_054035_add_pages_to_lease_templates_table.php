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
        Schema::table('lease_templates', function (Blueprint $table) {
            $table->json('pages')->nullable()->after('content');
            $table->integer('total_pages')->default(0)->after('pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lease_templates', function (Blueprint $table) {
            $table->dropColumn(['pages', 'total_pages']);
        });
    }
};
