<?php
// app/Console/Commands/SyncEmails.php

namespace App\Console\Commands;

use App\Models\EmailAccount;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncEmails extends Command
{
    protected $signature = 'emails:sync {--account=* : Specific account IDs to sync}';
    protected $description = 'Sync emails from IMAP servers';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    public function handle()
    {
        $this->info('Starting email synchronization...');

        $accounts = $this->option('account')
            ? EmailAccount::whereIn('id', $this->option('account'))->where('is_active', true)->get()
            : EmailAccount::where('is_active', true)->get();

        if ($accounts->isEmpty()) {
            $this->error('No active email accounts found');
            return 1;
        }

        foreach ($accounts as $account) {
            $this->info("Syncing account: {$account->email}");

            try {
                // Sync different folders
                $folders = ['INBOX', '[Gmail]/Sent Mail', '[Gmail]/Drafts'];

                foreach ($folders as $folder) {
                    $this->line("  Syncing folder: {$folder}");
                    $result = $this->emailService->syncEmails($account, $folder, 100);

                    if ($result) {
                        $this->info("  ✓ {$folder} synced successfully");
                    } else {
                        $this->error("  ✗ Failed to sync {$folder}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("  Error: {$e->getMessage()}");
                Log::error("Email sync error for {$account->email}: " . $e->getMessage());
            }
        }

        $this->info('Email synchronization completed!');
        return 0;
    }
}
