<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Location data
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable(); // km/h
            $table->decimal('bearing', 8, 2)->nullable();
            $table->decimal('altitude', 10, 2)->nullable();

            // Device info
            $table->string('device_id')->nullable();
            $table->string('network_type')->nullable();
            $table->integer('signal_strength')->nullable();
            $table->string('battery_level')->nullable();
            $table->boolean('is_mock_location')->default(false);
            $table->string('activity_type')->nullable();

            // Status tracking
            $table->enum('status', ['active', 'idle', 'offline'])->default('active');
            $table->timestamp('tracked_at');

            $table->timestamps();

            // Indexes for performance
            $table->index(['team_id', 'status', 'tracked_at'], 'team_status_tracked_index');
            $table->index(['user_id', 'tracked_at'], 'user_tracked_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_locations');
    }
};
