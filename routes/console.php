<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('otp:refresh-waiting --limit=50')->everyMinute()->withoutOverlapping();
Schedule::command('payments:reconcile-pending --limit=50')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->when(fn () => (bool) env('PAYMENT_AUTO_RECONCILE_ENABLED', true));
Schedule::command('smsbower:sync-catalog')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->when(fn () => (bool) env('SMSBOWER_SYNC_ENABLED', true));
Schedule::command('ops:check-alerts')->everyFiveMinutes()->withoutOverlapping();
