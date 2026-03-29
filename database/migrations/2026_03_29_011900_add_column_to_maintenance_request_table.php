<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('property_id')->constrained('units')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->after('unit_id')->constrained('rooms')->nullOnDelete();
            $table->foreignId('bed_id')->nullable()->after('room_id')->constrained('beds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['room_id']);
            $table->dropForeign(['bed_id']);
            $table->dropColumn(['unit_id', 'room_id', 'bed_id']);
        });
    }
};
