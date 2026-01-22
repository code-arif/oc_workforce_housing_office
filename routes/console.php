<?php

use App\Models\EmailMessage;
use App\Models\TeamLocation;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ============================================
// EMAIL SYNC SCHEDULER
// ============================================

// Sync emails every 5 minutes
Schedule::command('emails:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Email sync completed successfully');
    })
    ->onFailure(function () {
        Log::error('Email sync failed');
    });

// Clean up old trash emails (older than 30 days)
Schedule::call(function () {
    $deleted = EmailMessage::where('folder', 'trash')
        ->where('updated_at', '<', now()->subDays(30))
        ->forceDelete();

    Log::info("Cleaned up {$deleted} old trash emails");
})->daily()->at('02:00');

// Clean up temporary attachments
Schedule::call(function () {
    Storage::deleteDirectory('temp_attachments');
    Storage::makeDirectory('temp_attachments');

    Log::info('Cleaned up temporary attachments');
})->daily()->at('03:00');

// ============================================
// EXPIRED LEASES CHECKER
// ============================================

// Check for expired leases daily at midnight
// Updates lease status to COMPLETED, frees up beds, and notifies tenant/admin
Schedule::command('leases:check-expired')
    ->daily()
    ->at('00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Expired leases check completed successfully');
    })
    ->onFailure(function () {
        Log::error('Expired leases check failed');
    });

Schedule::command('invoice:check-unpaid')
    ->daily()
    ->at('01:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Unpaid invoices check completed successfully');
    })
    ->onFailure(function () {
        Log::error('Unpaid invoices check failed');
    });

// Optional: Database backup before cleanup
Schedule::call(function () {
    Artisan::call('backup:run --only-db');
})->weekly()->sundays()->at('01:00');

Schedule::command('emails:cleanup-trash')
    ->weekly()
    ->sundays()
    ->at('02:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Weekly trash email cleanup completed successfully');
    })
    ->onFailure(function () {
        Log::error('Weekly trash email cleanup failed');
    });