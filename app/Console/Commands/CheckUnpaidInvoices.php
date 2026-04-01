<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Tenant\UnpaidInvoiceReminderMail;

class CheckUnpaidInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:check-unpaid 
                            {--dry-run : Run without making changes}
                            {--no-mail : Skip sending notification emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for unpaid/overdue invoices and send reminders to tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for unpaid/overdue invoices...');

        $isDryRun = $this->option('dry-run');
        $skipMail = $this->option('no-mail');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No emails will be sent.');
        }

        if ($skipMail) {
            $this->warn('NO-MAIL MODE: Skipping email notifications.');
        }

        // Find all unpaid/overdue invoices that have passed their due date
        $unpaidInvoices = Invoice::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->with([
                'tenant.profile',
                'lease.property',
                'lease.assignments.bed',
                'payments'
            ])
            ->get();

        if ($unpaidInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return 0;
        }

        $this->info("Found {$unpaidInvoices->count()} overdue invoice(s).");

        $successCount = 0;
        $failCount = 0;

        foreach ($unpaidInvoices as $invoice) {
            $tenant = $invoice->tenant;
            $tenantName = ($tenant->profile->first_name ?? 'Unknown') . ' ' . ($tenant->profile->last_name ?? '');

            $this->line("Processing invoice #{$invoice->invoice_number} for tenant: {$tenantName}");

            if (!$tenant) {
                $this->warn("  ⚠ Tenant not found for invoice #{$invoice->id}");
                $failCount++;
                continue;
            }

            if (!$tenant->email) {
                $this->warn("  ⚠ Tenant {$tenantName} has no email address.");
                $failCount++;
                continue;
            }

            // Update invoice status to OVERDUE if it's still UNPAID
            if ($invoice->status === 'UNPAID') {
                if (!$isDryRun) {
                    $invoice->update(['status' => 'OVERDUE']);
                    $this->info("  ✓ Invoice status updated to OVERDUE");
                } else {
                    $this->info("  [DRY RUN] Would update invoice status to OVERDUE");
                }
            }

            if ($isDryRun || $skipMail) {
                $this->info("  [" . ($isDryRun ? 'DRY RUN' : 'NO-MAIL') . "] Would send reminder email to: {$tenant->email}");
                $successCount++;
                continue;
            }

            try {
                Mail::to($tenant->email)->send(new UnpaidInvoiceReminderMail($invoice, $tenant));

                $this->info("  ✓ Reminder email sent to: {$tenant->email}");
                $successCount++;

                Log::info("Unpaid invoice reminder sent", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_email' => $tenant->email,
                    'amount_due' => $invoice->balance_due,
                ]);

            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send email: " . $e->getMessage());
                $failCount++;

                Log::error("Failed to send unpaid invoice reminder", [
                    'invoice_id' => $invoice->id,
                    'tenant_email' => $tenant->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Total overdue invoices: {$unpaidInvoices->count()}");
        $this->info("Successfully processed: {$successCount}");
        if ($failCount > 0) {
            $this->error("Failed: {$failCount}");
        }

        return $failCount > 0 ? 1 : 0;
    }
}
