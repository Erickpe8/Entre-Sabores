<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Calidad WebP (0-100)
    |--------------------------------------------------------------------------
    */

    'quality' => (int) env('PROFILE_PHOTO_WEBP_QUALITY', 78),

    /*
    |--------------------------------------------------------------------------
    | Tamaños generados (px, cuadrados). "full" es el que se guarda en users.profile_photo
    |--------------------------------------------------------------------------
    |
    | Archivos en el mismo directorio:
    |   - avatar.webp         → full
    |   - avatar_medium.webp  → medium
    |   - avatar_thumb.webp   → thumb
    */

    'sizes' => [
        'thumb' => 50,
        'medium' => 150,
        'full' => 300,
    ],

    'filenames' => [
        'full' => 'avatar.webp',
        'medium' => 'avatar_medium.webp',
        'thumb' => 'avatar_thumb.webp',
    ],
];
