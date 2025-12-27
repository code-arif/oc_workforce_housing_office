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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('bed_number'); // e.g., "A", "B" (Top/Bottom bunk)
            $table->string('bed_label')->nullable();
            $table->decimal('base_rent', 10, 2)->default(0); 
            $table->boolean('is_occupied')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['room_id', 'bed_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
