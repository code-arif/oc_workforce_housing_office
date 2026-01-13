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
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained()->onDelete('cascade');
            $table->string('message_id')->unique();
            $table->string('thread_id')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->json('to'); // Array of recipients
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->text('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->json('attachments')->nullable();
            $table->enum('folder', ['inbox', 'sent', 'drafts', 'starred', 'trash', 'spam'])->default('inbox');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->timestamp('email_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email_account_id', 'folder']);
            $table->index('is_starred');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_messages');
    }
};
