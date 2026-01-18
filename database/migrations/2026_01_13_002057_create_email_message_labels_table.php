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
        Schema::create('email_message_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_message_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_label_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['email_message_id', 'email_label_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_message_labels');
    }
};
