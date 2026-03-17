<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send notifications for deals closing in 3 days
Schedule::command('notifications:deals-closing-soon --days=3')->dailyAt('08:00');
