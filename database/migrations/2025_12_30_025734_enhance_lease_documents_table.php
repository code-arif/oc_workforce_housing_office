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
        Schema::table('lease_documents', function (Blueprint $table) {
            $table->longText('generated_document_content')->nullable()->after('document_url');
            $table->json('field_values')->nullable()->comment('Captured field values at signing time')->after('generated_document_content');
            $table->longText('tenant_signature_image')->nullable()->after('tenant_signature_token');
            $table->longText('admin_signature_image')->nullable()->after('admin_signature_token');
            $table->json('signature_metadata')->nullable()->comment('Device, location, timestamp data')->after('admin_signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lease_documents', function (Blueprint $table) {
            $table->dropColumn([
                'generated_document_content',
                'field_values',
                'tenant_signature_image',
                'admin_signature_image',
                'signature_metadata'
            ]);
        });
    }
};
