<?php

namespace App\Console\Commands;

use App\Models\Bed;
use App\Models\Lease;
use App\Models\LeaseAssignment;
use App\Mail\Tenant\LeaseCompletedMail;
use App\Mail\Tenant\LeaseCompletedAdminMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiredLeases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leases:check-expired 
                            {--dry-run : Run without making changes}
                            {--no-mail : Skip sending notification emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired leases and update their status to completed, free up beds, and notify tenants and admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired leases...');

        $isDryRun = $this->option('dry-run');
        $skipMail = $this->option('no-mail');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No changes will be made.');
        }

        // Find all active leases that have passed their end date
        $expiredLeases = Lease::where('status', 'ACTIVE')
            ->whereDate('end_date', '<', now()->toDateString())
            ->with(['tenant.profile', 'property', 'assignments.bed'])
            ->get();

        if ($expiredLeases->isEmpty()) {
            $this->info('No expired leases found.');
            return 0;
        }

        $this->info("Found {$expiredLeases->count()} expired lease(s).");

        $successCount = 0;
        $failCount = 0;

        foreach ($expiredLeases as $lease) {
            $tenantName = ($lease->tenant->profile->first_name ?? 'Unknown') . ' ' . ($lease->tenant->profile->last_name ?? '');
            $this->line("Processing lease #{$lease->id} for tenant: {$tenantName}");

            if ($isDryRun) {
                $this->info("  [DRY RUN] Would update lease #{$lease->id} to COMPLETED");
                $this->info("  [DRY RUN] Would update lease assignments to is_current = false");
                $this->info("  [DRY RUN] Would update beds to is_occupied = false");
                if (!$skipMail) {
                    $this->info("  [DRY RUN] Would send notification emails");
                }
                $successCount++;
                continue;
            }

            try {
                DB::beginTransaction();

                // 1. Update lease status to COMPLETED
                $lease->update([
                    'status' => 'COMPLETED',
                ]);
                $this->info("  ✓ Lease status updated to COMPLETED");

                // 2. Update all lease assignments to is_current = false and set actual_move_out
                $lease->assignments()->update([
                    'is_current' => false,
                    'actual_move_out' => $lease->end_date,
                ]);
                $this->info("  ✓ Lease assignments marked as not current");

                // 3. Update all assigned beds to is_occupied = false
                $bedIds = $lease->assignments->pluck('bed_id')->filter()->toArray();
                if (!empty($bedIds)) {
                    Bed::whereIn('id', $bedIds)->update([
                        'is_occupied' => false,
                    ]);
                    $this->info("  ✓ Beds marked as unoccupied (IDs: " . implode(', ', $bedIds) . ")");
                }

                DB::commit();

                // 4. Send notification emails
                if (!$skipMail) {
                    $this->sendNotificationEmails($lease);
                }

                $successCount++;
                Log::info("Lease #{$lease->id} marked as completed. Tenant: {$tenantName}");

            } catch (\Exception $e) {
                DB::rollBack();
                $failCount++;
                $this->error("  ✗ Error processing lease #{$lease->id}: {$e->getMessage()}");
                Log::error("Failed to process expired lease #{$lease->id}: {$e->getMessage()}", [
                    'lease_id' => $lease->id,
                    'exception' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Total expired leases: {$expiredLeases->count()}");
        $this->info("Successfully processed: {$successCount}");
        if ($failCount > 0) {
            $this->error("Failed: {$failCount}");
        }

        return $failCount > 0 ? 1 : 0;
    }

    /**
     * Send notification emails to tenant and admin
     */
    protected function sendNotificationEmails(Lease $lease): void
    {
        try {
            // Send email to tenant
            if ($lease->tenant && $lease->tenant->email) {
                Mail::to($lease->tenant->email)->send(new LeaseCompletedMail($lease));
                $this->info("  ✓ Notification email sent to tenant: {$lease->tenant->email}");
            } else {
                $this->warn("  ⚠ No tenant email found, skipping tenant notification");
            }

            // Send email to admin
            $adminEmail = config('mail.admin_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new LeaseCompletedAdminMail($lease));
                $this->info("  ✓ Notification email sent to admin: {$adminEmail}");
            } else {
                $this->warn("  ⚠ No admin email configured, skipping admin notification");
            }

        } catch (\Exception $e) {
            $this->error("  ✗ Failed to send notification emails: {$e->getMessage()}");
            Log::error("Failed to send lease completion emails for lease #{$lease->id}: {$e->getMessage()}");
        }
    }
}
