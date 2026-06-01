<?php

namespace App\Providers;

use App\Services\DomainCheck\CheckLogPruner;
use App\Services\DomainCheck\DomainStatusStore;
use App\Services\DomainCheck\DomainStatusUpdater;
use App\Services\Telegram\TelegramApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramApiService::class);
        $this->app->singleton(DomainStatusStore::class);
        $this->app->singleton(DomainStatusUpdater::class);
        $this->app->singleton(CheckLogPruner::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
