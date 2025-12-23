<?php

use App\Models\TeamLocation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Command define
Artisan::command('locations:cleanup', function () {
    $deleted = TeamLocation::where('tracked_at', '<', now()->subDays(7))->delete();
    $this->info("Deleted {$deleted} old location records!");
})->purpose('Delete location data older than 7 days');

// Schedule it to run daily
Schedule::command('locations:cleanup')->daily();

