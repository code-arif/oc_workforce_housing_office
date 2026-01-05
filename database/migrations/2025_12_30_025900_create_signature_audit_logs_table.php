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
        Schema::create('signature_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_document_id')->constrained()->cascadeOnDelete();
            $table->string('action')->comment('signed, viewed, downloaded, etc.');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->comment('tenant, admin');
            $table->string('user_ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable()->comment('Device info, location, etc.');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('lease_document_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_audit_logs');
    }
};
