<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('security.headers.enabled', true)) {
            return $response;
        }

        $geolocationPolicy = $request->routeIs('register') ? 'geolocation=(self)' : 'geolocation=()';

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            "accelerometer=(), camera=(), {$geolocationPolicy}, gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()"
        );

        if ($this->shouldSendCsp()) {
            $policy = config('security.csp.policy');
            if (! is_string($policy) || $policy === '') {
                $policy = (string) config('security.csp_default_policy');
            }
            $response->headers->set('Content-Security-Policy', $policy);
        }

        $response->headers->remove('X-Powered-By');

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function shouldSendCsp(): bool
    {
        $explicit = config('security.csp.enabled');

        if ($explicit !== null && $explicit !== '') {
            return filter_var($explicit, FILTER_VALIDATE_BOOLEAN);
        }

        return app()->environment('production');
    }
}
