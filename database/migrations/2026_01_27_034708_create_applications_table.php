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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected'
            ])->default('pending');

            // Applicant info (for individual OR company contact person)
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();

            // Corporate only fields
            $table->string('company_name')->nullable();
            $table->string('industry')->nullable();
            $table->text('company_address')->nullable();
            $table->integer('employee_count')->nullable();

            // Reservation details (stored as JSON)
            $table->json('reservation_item')->nullable();

            // Additional notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for better query performance
            $table->index('status');
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
