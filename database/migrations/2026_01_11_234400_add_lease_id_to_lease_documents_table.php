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
        Schema::table('lease_documents', function (Blueprint $table) {
            $table->foreignId('lease_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lease_documents', function (Blueprint $table) {
            $table->dropForeign(['lease_id']);
            $table->dropColumn('lease_id');
        });
    }
};
