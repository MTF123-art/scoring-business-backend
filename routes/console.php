<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:refresh-instagram-tokens')
    ->dailyAt('00:00');

Schedule::command('app:run-daily-metrics-pipeline')
    ->dailyAt('00:00')
    ->withoutOverlapping();
