<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Análisis de maridaje — API compatible OpenAI (POST {base_url}/chat/completions).
    | Credenciales solo vía .env (MARIDAJE_AI_*); nunca en código ni en el frontend.
    | Sin API key el servicio devuelve null; el Job persiste un fallback en posts.ai_analysis.
    */
    'maridaje_ai' => [
        'api_key' => env('MARIDAJE_AI_API_KEY'),
        'base_url' => env('MARIDAJE_AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('MARIDAJE_AI_MODEL', 'gpt-4o-mini'),
        'timeout' => env('MARIDAJE_AI_TIMEOUT', 90),
    ],

];
