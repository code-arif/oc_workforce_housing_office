<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#3b82f6'); // Default blue
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('google_calendar_id')->nullable()->unique();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_visible']);
        });

        // Add calendar_id to works table
        Schema::table('works', function (Blueprint $table) {
            $table->foreignId('calendar_id')->nullable()->after('category_id')
                ->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained()->onDelete('cascade');

            $table->index(['calendar_id', 'start_datetime']);
            $table->index(['user_id', 'calendar_id']);
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropForeign(['calendar_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['calendar_id', 'user_id']);
        });

        Schema::dropIfExists('calendars');
    }
};
