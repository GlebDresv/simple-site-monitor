<?php

use App\Services\DomainCheck\DomainCheckScheduler;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('domain-check:schedule', function (DomainCheckScheduler $scheduler) {
    $scheduler->run();
    $this->info('Domain check jobs dispatched.');
})->purpose('Dispatch domain check jobs for the current minute interval');

Schedule::call(fn () => app(DomainCheckScheduler::class)->run())
    ->everyMinute()
    ->name('domain-check-scheduler')
    ->withoutOverlapping();

Schedule::command('check-logs:prune')
    ->daily()
    ->name('check-logs-prune')
    ->withoutOverlapping();
