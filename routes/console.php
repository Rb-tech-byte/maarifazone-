<?php

/**
 * Artisan Console & Scheduler Definitions
 *
 * Registers scheduled background tasks for live class reminders and learning sync in Maarifazone.
 *
 * @author Renatus Bernardo
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send live class reminder notifications every 5 minutes
Schedule::command('live-classes:send-reminders')->everyFiveMinutes();
