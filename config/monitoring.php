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

    /*
    |--------------------------------------------------------------------------
    | Vercel Cron (Bearer CRON_SECRET en Authorization)
    |--------------------------------------------------------------------------
    |
    | Vercel envía Authorization: Bearer <CRON_SECRET> al invocar crons
    | definidos en vercel.json. Obligatorio en producción en Vercel.
    |
    */

    'cron_secret' => env('CRON_SECRET'),

];
