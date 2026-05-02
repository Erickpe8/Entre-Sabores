<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cabeceras HTTP de seguridad (X-Frame-Options, CSP, etc.)
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content-Security-Policy
    |--------------------------------------------------------------------------
    |
    | Por defecto solo se aplica en entorno production (Vite en desarrollo usa
    | WebSocket y rompe un CSP estricto). Override con SECURITY_CSP_ENABLED.
    |
    | SECURITY_CSP_POLICY permite sustituir la política completa (cadena única).
    |
    */

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED'),
        'policy' => env('SECURITY_CSP_POLICY'),
    ],

    /*
    | Política base si no defines SECURITY_CSP_POLICY. Incluye CDNs usados en
    | layouts (fonts.bunny.net, cdnjs Font Awesome, Google Fonts en welcome).
    */

    'csp_default_policy' => implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com https://cdnjs.cloudflare.com",
        "font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com data:",
        "img-src 'self' data: blob: https:",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ]),

];
