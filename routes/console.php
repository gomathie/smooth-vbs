<?php

use App\Console\Commands\SyncGpsLocations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pull vehicle locations from all active GPS providers every minute.
// Add this to your server crontab:
//   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
Schedule::command(SyncGpsLocations::class)->everyMinute()->withoutOverlapping();
