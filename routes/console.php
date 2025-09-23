<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:refresh-instagram-tokens')
    ->dailyAt('00:00');

Schedule::command('app:fetch-instagram-metrics')
    ->dailyAt('00:00');

Schedule::command('app:fetch-facebook-metrics')
    ->dailyAt('00:00');

Schedule::command('app:calculate-scores')
    ->dailyAt('00:00');
