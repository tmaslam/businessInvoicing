<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Behind Cloudflare SSL every generated URL must be https.
        if (str_starts_with((string) config('app.url'), 'https://') || $this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
