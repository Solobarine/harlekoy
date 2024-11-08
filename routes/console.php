<?php

use App\Jobs\HandleUserUpdate;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Get Updates from Cache
$updates = Cache::has('user_updates') && Cache::get('user_updates')->count() > 0 ? Cache::pull('user_updates') : [];

// Schedule Job
Schedule::job(new HandleUserUpdate($updates))->hourly()->when(function ($updates) {
    count($updates) > 0;
});
