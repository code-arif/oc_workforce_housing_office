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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->cascadeOnDelete(); // Assuming 'properties' table exists based on lease schema
            $table->string('unit')->nullable(); // To store unit name/code, assuming no separate units table provided
            $table->string('title')->nullable();
            $table->enum('category', ['ac', 'appliance', 'electrical', 'heat', 'kitchen', 'plumbing', 'other']);
            $table->text('description')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('grant_permission')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // For admin/owner created requests
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
