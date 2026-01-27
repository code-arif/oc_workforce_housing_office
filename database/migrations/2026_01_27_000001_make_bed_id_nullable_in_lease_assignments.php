<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration allows leases to be created and signed without 
     * a bed assignment. The bed can be assigned later when tenant arrives.
     */
    public function up(): void
    {
        // Add bed_assignment_pending flag to leases table
        Schema::table('leases', function (Blueprint $table) {
            $table->boolean('bed_assignment_pending')->default(false)->after('status');
        });

        // Make bed_id nullable in lease_assignments
        // Note: We need to drop the foreign key first, modify the column, then re-add the foreign key
        Schema::table('lease_assignments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['bed_id']);
        });

        Schema::table('lease_assignments', function (Blueprint $table) {
            // Make bed_id nullable
            $table->unsignedBigInteger('bed_id')->nullable()->change();
        });

        Schema::table('lease_assignments', function (Blueprint $table) {
            // Re-add the foreign key constraint with nullable support
            $table->foreign('bed_id')->references('id')->on('beds')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn('bed_assignment_pending');
        });

        // Note: Reverting this may fail if there are null bed_id records
        Schema::table('lease_assignments', function (Blueprint $table) {
            $table->dropForeign(['bed_id']);
        });

        Schema::table('lease_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('bed_id')->nullable(false)->change();
        });

        Schema::table('lease_assignments', function (Blueprint $table) {
            $table->foreign('bed_id')->references('id')->on('beds')->cascadeOnDelete();
        });
    }
};
