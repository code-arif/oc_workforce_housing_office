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
        Schema::create('lease_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('lease_templates')->nullOnDelete();
            $table->text('document_url')->nullable();
            $table->longText('tenant_signature_token')->nullable();
            $table->timestamp('tenant_signed_at')->nullable();
            $table->string('tenant_ip_address')->nullable();
            $table->longText('admin_signature_token')->nullable();
            $table->timestamp('admin_signed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_documents');
    }
};
