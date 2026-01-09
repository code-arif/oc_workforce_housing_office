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
            $table->string('document_path')->nullable()->after('file_type');
            $table->string('thumbnail_path')->nullable()->after('document_path');
            $table->text('description')->nullable()->after('name');
            $table->json('metadata')->nullable()->after('placeholders'); // Store file metadata like page count, size, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lease_templates', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'thumbnail_path', 'description', 'metadata']);
        });
    }
};
