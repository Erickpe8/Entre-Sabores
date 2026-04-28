<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sesiones en tabla + SQLite en solo lectura (típico en Docker) rompe cada request.
        if (config('session.driver') === 'database' && config('database.default') === 'sqlite') {
            config(['session.driver' => 'file']);
        }

        $this->forceHttpsInProduction();
    }

    private function forceHttpsInProduction(): void
    {
        if (
            filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN)
            || $this->app->environment(['production', 'qa'])
        ) {
            URL::forceScheme('https');
        }
    }
}
