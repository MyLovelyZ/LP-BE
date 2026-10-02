<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Admin panel tokens expire after 7 days; clear them out of the database once a day
Schedule::command('sanctum:prune-expired --hours=24')->daily();
