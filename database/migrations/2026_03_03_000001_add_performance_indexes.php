<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for scalability at 100K+ records
 * This migration adds missing indexes to optimize query performance
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Leases table indexes
        Schema::table('leases', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_leases_tenant_id');
            $table->index('status', 'idx_leases_status');
            $table->index('start_date', 'idx_leases_start_date');
            $table->index('end_date', 'idx_leases_end_date');
            $table->index(['status', 'end_date'], 'idx_leases_status_end_date'); // For expiring leases query
            $table->index(['property_id', 'status'], 'idx_leases_property_status'); // For property-based lease queries
        });

        // Lease assignments table indexes
        Schema::table('lease_assignments', function (Blueprint $table) {
            $table->index('is_current', 'idx_lease_assignments_is_current');
            $table->index(['lease_id', 'is_current'], 'idx_lease_assignments_lease_current');
            $table->index(['bed_id', 'is_current'], 'idx_lease_assignments_bed_current'); // For bed availability
        });

        // Lease payment schedules indexes
        Schema::table('lease_payment_schedules', function (Blueprint $table) {
            $table->index('due_date', 'idx_lease_payment_schedules_due_date');
            $table->index(['lease_id', 'due_date'], 'idx_lease_payment_schedules_lease_due');
        });

        // Tenants table indexes
        Schema::table('tenants', function (Blueprint $table) {
            $table->index('status', 'idx_tenants_status');
            $table->index('move_in_date', 'idx_tenants_move_in_date');
        });

        // Tenant profiles table - ensure foreign key index
        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->index('tenant_id', 'idx_tenant_profiles_tenant_id');
        });

        // Units table
        Schema::table('units', function (Blueprint $table) {
            $table->index('is_active', 'idx_units_is_active');
            $table->index(['property_id', 'is_active'], 'idx_units_property_active');
        });

        // Rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->index('is_active', 'idx_rooms_is_active');
            $table->index(['unit_id', 'is_active'], 'idx_rooms_unit_active');
        });

        // Beds table
        Schema::table('beds', function (Blueprint $table) {
            $table->index('is_occupied', 'idx_beds_is_occupied');
            $table->index(['room_id', 'is_occupied'], 'idx_beds_room_occupied');
        });

        // Maintenance requests
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->index('status', 'idx_maintenance_requests_status');
            $table->index(['property_id', 'status'], 'idx_maintenance_requests_property_status');
        });

        // Transactions table (if exists)
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('created_at', 'idx_transactions_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndex('idx_leases_tenant_id');
            $table->dropIndex('idx_leases_status');
            $table->dropIndex('idx_leases_start_date');
            $table->dropIndex('idx_leases_end_date');
            $table->dropIndex('idx_leases_status_end_date');
            $table->dropIndex('idx_leases_property_status');
        });

        Schema::table('lease_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_lease_assignments_is_current');
            $table->dropIndex('idx_lease_assignments_lease_current');
            $table->dropIndex('idx_lease_assignments_bed_current');
        });

        Schema::table('lease_payment_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_lease_payment_schedules_due_date');
            $table->dropIndex('idx_lease_payment_schedules_lease_due');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('idx_tenants_status');
            $table->dropIndex('idx_tenants_move_in_date');
        });

        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_profiles_tenant_id');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('idx_units_is_active');
            $table->dropIndex('idx_units_property_active');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('idx_rooms_is_active');
            $table->dropIndex('idx_rooms_unit_active');
        });

        Schema::table('beds', function (Blueprint $table) {
            $table->dropIndex('idx_beds_is_occupied');
            $table->dropIndex('idx_beds_room_occupied');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndex('idx_maintenance_requests_status');
            $table->dropIndex('idx_maintenance_requests_property_status');
        });

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_created_at');
            });
        }
    }
};
