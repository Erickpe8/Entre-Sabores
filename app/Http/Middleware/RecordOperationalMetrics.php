<?php

namespace App\Http\Middleware;

use App\Support\OperationalMetrics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordOperationalMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('health', 'internal/metrics', 'up')) {
            return $next($request);
        }

        OperationalMetrics::incrementHttpRequests();

        $response = $next($request);

        if ($response->getStatusCode() >= 500) {
            OperationalMetrics::incrementHttpErrors();
        }

        return $response;
    }
}
