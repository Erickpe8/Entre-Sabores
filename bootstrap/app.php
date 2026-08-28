<?php

use App\Http\Middleware\RecordOperationalMetrics;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trusted = config('app.trusted_proxies');
        if (filled($trusted)) {
            $at = str_contains((string) $trusted, ',')
                ? array_values(array_filter(array_map('trim', explode(',', (string) $trusted))))
                : trim((string) $trusted);
            $middleware->trustProxies(at: $at);
        }

        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->appendToGroup('web', RecordOperationalMetrics::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e): void {
            if (! filter_var(config('monitoring.structured_exception_logging'), FILTER_VALIDATE_BOOLEAN)) {
                return;
            }

            try {
                Log::channel('structured')->error('exception.report', [
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            } catch (Throwable) {
                //
            }
        });
    })->create();
