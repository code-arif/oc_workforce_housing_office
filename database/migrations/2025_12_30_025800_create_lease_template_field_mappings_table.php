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
        Schema::create('lease_template_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('lease_templates')->cascadeOnDelete();
            $table->string('field_placeholder')->comment('e.g., {{TENANT_NAME}}, {{PROPERTY_ADDRESS}}');
            $table->string('field_label')->comment('Human-readable label');
            $table->enum('field_type', ['TEXT', 'DATE', 'AMOUNT', 'SIGNATURE', 'CUSTOM'])->default('TEXT');
            $table->string('tenant_data_source')->comment('e.g., tenant.full_name, property.address, lease.rent_amount');
            $table->boolean('is_required')->default(true);
            $table->json('placeholder_position')->nullable()->comment('{page: 1, x: 100, y: 200}');
            $table->softDeletes();
            $table->timestamps();

            $table->index('template_id');
            $table->unique(['template_id', 'field_placeholder'], 'lt_fm_template_placeholder_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_template_field_mappings');
    }
};
