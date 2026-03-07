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

            // Applicant info (for individual OR company contact person)
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->string('country_of_origin')->nullable();
           
            $table->date('date_of_birth')->nullable();
            $table->date('arrival_date')->nullable();
            $table->date('departure_date')->nullable();

            $table->string('passport_copy')->nullable();
            $table->string('visa_document')->nullable();
            $table->string('front_id_document')->nullable();
            $table->string('back_id_document')->nullable();

            // Additional notes
            $table->text('notes')->nullable();

            // Application details
            $table->string('application_type')->nullable();
            $table->string('application_number')->nullable();
            $table->string('application_status')->nullable();

            //Property preferences
            $table->bigInteger('property_id')->unsigned()->nullable();

            //employer/sponsor information
            $table->json('employer_info')->nullable(); // Store employer info as JSON (company name, contact person, etc.)

            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_city')->nullable();
            $table->string('sponsor_state')->nullable();
            $table->string('sponsor_zipcode')->nullable();
            $table->string('sponsor_country')->nullable();
            $table->string('sponsor_phone')->nullable();
            $table->string('sponsor_email')->nullable();
            $table->string('sponsor_relationship')->nullable();
            $table->string('is_j1_sponsor')->nullable();

            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected'
            ])->default('pending');

            $table->timestamps();
            

            // Indexes for better query performance
            $table->index('status');
            $table->index('email');
            $table->index('phone');
            $table->index('property_id');
            $table->index('application_number');
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
