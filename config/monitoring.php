<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Métricas operativas (contadores en Redis / cache)
    |--------------------------------------------------------------------------
    |
    | Desactivar en tests o si no usas Redis como CACHE_STORE en prod.
    |
    */

    'metrics_enabled' => env('MONITORING_METRICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Health check con token opcional
    |--------------------------------------------------------------------------
    |
    | Si HEALTH_CHECK_TOKEN está definido, GET /health debe incluir
    | ?token=... o cabecera X-Health-Token (útil para no exponer detalle en público).
    |
    */

    'health_token' => env('HEALTH_CHECK_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Logs estructurados adicionales para errores no capturados
    |--------------------------------------------------------------------------
    */

    'structured_exception_logging' => env('LOG_STRUCTURED_EXCEPTIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Token para GET /internal/metrics (Bearer o ?token=)
    |--------------------------------------------------------------------------
    */

    'metrics_token' => env('METRICS_TOKEN'),

    'cron_secret' => env('CRON_SECRET'),

];
