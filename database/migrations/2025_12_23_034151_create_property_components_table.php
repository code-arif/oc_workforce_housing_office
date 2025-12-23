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
        Schema::create('property_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_type_id')
                ->constrained('property_types')
                ->cascadeOnDelete();

            $table->string('name')->index();
            $table->string('slug');
            $table->unsignedInteger('level_order');
            $table->boolean('is_repeatable')->default(true);

            $table->timestamps();

            // same property type er moddhe duplicate component prevent korbe
            $table->unique(['property_type_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_components');
    }
};
