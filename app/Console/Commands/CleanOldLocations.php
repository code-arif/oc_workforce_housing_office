<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeamLocation;

class CleanOldLocations extends Command
{
    protected $signature = 'locations:cleanup';
    protected $description = 'Delete location data older than 7 days';

    public function handle()
    {
        $deleted = TeamLocation::where('tracked_at', '<', now()->subDays(7))->delete();

        $this->info("✅ Deleted {$deleted} old location records!");

        return Command::SUCCESS;
    }
}
