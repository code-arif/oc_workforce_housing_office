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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // DateTime fields (Google Calendar sync)
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->boolean('is_all_day')->default(false);

            // Status and flags
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_rescheduled')->default(false);
            $table->text('note')->nullable();

            // Relations
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');

            // Google Calendar sync fields
            $table->string('google_event_id')->nullable()->unique();
            $table->timestamp('google_synced_at')->nullable();

            // $table->decimal('geofence_radius', 8, 2)->default(50);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
