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
        Schema::create('tenant_employment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');

            $table->string('employment_status')->nullable();

            // employed
            $table->string('employer')->nullable();
            $table->string('title')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_current_working')->default(false);

            // student
            $table->string('school')->nullable();
            $table->date('start_date')->nullable();
            $table->date('graduation_date')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_employment_histories');
    }
};
