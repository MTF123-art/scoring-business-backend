<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:refresh-instagram-tokens')
    ->everyMinute();

Schedule::command('app:refresh-facebook-tokens')
    ->everyMinute();

Schedule::command('app:fetch-instagram-metrics')
    ->everyMinute();
