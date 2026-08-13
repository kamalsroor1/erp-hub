<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Run queue worker every minute (processes pending background jobs)
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping();

// 2. Restart queue worker gracefully every 6 hours to free RAM
Schedule::command('queue:restart')
    ->everySixHours();

// 3. Clear old Pulse monitoring entries older than 7 days
Schedule::command('pulse:clear --expired=7')
    ->daily();
