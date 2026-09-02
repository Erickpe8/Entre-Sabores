<?php

return [

    'default_description' => 'Comparte maridajes, descubre experiencias gastronómicas de distintas culturas y conecta con una comunidad global en Entre Sabores.',

    'default_og_image' => 'images/image-hero.png',

    'default_og_image_width' => 880,

    'default_og_image_height' => 880,

    'organization_name' => 'Entre Sabores',

    'auth_descriptions' => [
        'login' => 'Inicia sesión en Entre Sabores para publicar maridajes, seguir cuentas y participar en la comunidad gastronómica.',
        'register' => 'Crea tu cuenta en Entre Sabores y comparte maridajes, descubre culturas y conecta con gastrónomos de todo el mundo.',
    ],

    /*
    | Rutas públicas indexables (nombre de ruta => prioridad sitemap 0.0–1.0).
    */
    'sitemap_routes' => [
        'welcome' => '1.0',
        'explore' => '0.8',
        'how-it-works' => '0.8',
        'login' => '0.3',
        'register' => '0.5',
    ],

];
