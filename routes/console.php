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

// 4. Send daily EOD business summary report to Telegram at 11:59 PM
Schedule::command('notify:daily-summary')
    ->dailyAt('23:59');

// 5. Send daily low stock notification to Telegram at 09:00 AM
Schedule::command('notify:low-stock')
    ->dailyAt('09:00');

// 6. Check and alert for overdue open shifts to Telegram every 2 hours
Schedule::command('notify:overdue-shifts')
    ->everyTwoHours();
