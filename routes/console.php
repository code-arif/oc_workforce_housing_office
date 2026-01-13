<?php

use App\Models\EmailMessage;
use App\Models\TeamLocation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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
        \Log::info('Email sync completed successfully');
    })
    ->onFailure(function () {
        \Log::error('Email sync failed');
    });

// Clean up old trash emails (older than 30 days)
Schedule::call(function () {
    $deleted = EmailMessage::where('folder', 'trash')
        ->where('updated_at', '<', now()->subDays(30))
        ->forceDelete();

    \Log::info("Cleaned up {$deleted} old trash emails");
})->daily()->at('02:00');

// Clean up temporary attachments
Schedule::call(function () {
    \Storage::deleteDirectory('temp_attachments');
    \Storage::makeDirectory('temp_attachments');

    \Log::info('Cleaned up temporary attachments');
})->daily()->at('03:00');

// Optional: Database backup before cleanup
Schedule::call(function () {
    \Artisan::call('backup:run --only-db');
})->weekly()->sundays()->at('01:00');

