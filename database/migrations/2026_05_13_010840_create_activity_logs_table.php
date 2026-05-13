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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role')->nullable();
            $table->string('action');
            $table->string('module');
            $table->string('route')->nullable();
            $table->string('method')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->nullableMorphs('subject'); // Adds subject_type and subject_id
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->timestamps();

            // Indexes for faster filtering
            $table->index('user_id');
            $table->index('module');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
