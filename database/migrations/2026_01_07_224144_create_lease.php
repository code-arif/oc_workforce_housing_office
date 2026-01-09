<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_type'); // pdf or docx
            $table->longText('content')->nullable(); // Converted HTML content
            $table->json('placeholders')->nullable(); // Dynamic field placeholders
            $table->json('signatures')->nullable(); // Signature positions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lease_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->text('rendered_content'); // HTML with filled placeholders
            $table->string('admin_signature')->nullable();
            $table->string('tenant_signature')->nullable();
            $table->timestamp('admin_signed_at')->nullable();
            $table->timestamp('tenant_signed_at')->nullable();
            $table->enum('status', ['draft', 'pending_signatures', 'signed', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_documents');
        Schema::dropIfExists('lease_templates');
    }
};
