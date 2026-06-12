<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add PROCESSING to the enum
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('UNPAID', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED', 'PROCESSING') DEFAULT 'UNPAID'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('UNPAID', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED') DEFAULT 'UNPAID'");
    }
};
