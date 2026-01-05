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
        Schema::table('lease_templates', function (Blueprint $table) {
            $table->string('document_file_path')->nullable()->after('content');
            $table->enum('document_type', ['HTML', 'JSON', 'PDF'])->default('HTML')->after('document_file_path');
            $table->string('uploaded_file_original_name')->nullable()->after('document_type');
            $table->json('field_mappings')->nullable()->comment('Field mapping configuration')->after('uploaded_file_original_name');
            $table->boolean('is_editable')->default(true)->after('field_mappings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lease_templates', function (Blueprint $table) {
            $table->dropColumn([
                'document_file_path',
                'document_type',
                'uploaded_file_original_name',
                'field_mappings',
                'is_editable'
            ]);
        });
    }
};
