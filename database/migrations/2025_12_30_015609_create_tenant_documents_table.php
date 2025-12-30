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
        Schema::create('tenant_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');

            $table->enum('document_type', [
                'passport',
                'visa',
                'id_front',
                'id_back',
                'other'
            ]);

            $table->string('file_path');
            $table->string('file_original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_documents');
    }
};
