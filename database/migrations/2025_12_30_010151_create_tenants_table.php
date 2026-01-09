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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->enum('application_source', ['admin', 'self'])->default('self');
            $table->enum('status', ['pending', 'processing', 'under_review', 'approved', 'rejected', 'active', 'inactive'])->default('pending');

            $table->date('move_in_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // authentication
            $table->string('email')->unique();
            $table->string('password')->nullable();

            // password reset
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->longText('reset_password_token')->nullable();
            $table->timestamp('reset_password_token_expire_at')->nullable();

            // approval token
            $table->string('approval_token')->unique()->nullable();
            $table->timestamp('approval_token_expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
