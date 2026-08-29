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
    | Política base estricta: sin eval. JS solo desde Vite (public/build).
    | style-src conserva 'unsafe-inline' por estilos de componentes y atributos ocasionales.
    | Google Fonts (Plus Jakarta Sans en app.css): fonts.googleapis.com + fonts.gstatic.com.
    | Si Laravel Echo/Reverb usa otro host/puerto, amplía connect-src vía SECURITY_CSP_POLICY.
    */

    'csp_default_policy' => implode('; ', [
        "default-src 'self'",
        "script-src 'self'",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' data: https://fonts.gstatic.com",
        "img-src 'self' data: blob: https:",
        "connect-src 'self'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ]),

];
