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
    | Análisis de maridaje (API compatible OpenAI: POST {base_url}/chat/completions).
    | Ej.: OpenAI https://api.openai.com/v1 o DeepSeek https://api.deepseek.com/v1 (p. ej. modelo deepseek-chat).
    | Sin MARIDAJE_AI_API_KEY el Job omite la llamada y ai_analysis queda null.
    */
    'maridaje_ai' => [
        'api_key' => env('MARIDAJE_AI_API_KEY'),
        'base_url' => env('MARIDAJE_AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('MARIDAJE_AI_MODEL', 'gpt-4o-mini'),
        'timeout' => env('MARIDAJE_AI_TIMEOUT', 90),
    ],

];
