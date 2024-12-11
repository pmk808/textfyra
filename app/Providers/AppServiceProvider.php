<?php

namespace App\Providers;

use App\Services\VonageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VonageService::class, function ($app) {
            return new VonageService();
        });
    }
}
