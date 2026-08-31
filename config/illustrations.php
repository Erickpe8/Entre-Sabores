<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reglas de exportación (obligatorias)
    |--------------------------------------------------------------------------
    |
    | - PNG con canal alpha real (fondo 100% transparente).
    | - Sin lienzo blanco/gris, tarjetas ni sombras de canvas.
    | - Sombras solo en objetos/personajes.
    | - Validar: npm run illustrations:validate
    | - WebP opcional (derivado del PNG validado): npm run illustrations:webp
    |
    */

    'catalog' => [

        'hero-landing' => [
            'webp' => 'images/heroes/image-hero.webp',
            'png' => 'images/heroes/image-hero.png',
            'legacy' => 'images/image-hero.png',
            'alt' => 'Personaje explorando sabores de distintas culturas alrededor del mundo',
            'width' => 1200,
            'height' => 1200,
        ],

        'auth-welcome-food-world' => [
            'webp' => 'images/illustrations/auth/auth-welcome-food-world.webp',
            'png' => 'images/illustrations/auth/auth-welcome-food-world.png',
            'legacy' => 'images/hero-gallery.png',
            'alt' => 'Bienvenida a un mundo de sabores internacionales',
            'width' => 960,
            'height' => 1200,
        ],

        'auth-register-join-community' => [
            'webp' => 'images/illustrations/auth/auth-register-join-community.webp',
            'png' => 'images/illustrations/auth/auth-register-join-community.png',
            'legacy' => 'images/hero-gallery.png',
            'alt' => 'Únete a la comunidad gastronómica de Entre Sabores',
            'width' => 960,
            'height' => 1200,
        ],

        'auth-login-welcome-back' => [
            'webp' => 'images/illustrations/auth/auth-login-welcome-back.webp',
            'png' => 'images/illustrations/auth/auth-login-welcome-back.png',
            'legacy' => 'images/hero-gallery.png',
            'alt' => 'Bienvenido de vuelta a Entre Sabores',
            'width' => 284,
            'height' => 499,
        ],

        'empty-no-posts' => [
            'webp' => 'images/illustrations/empty-states/empty-no-posts.webp',
            'png' => 'images/illustrations/empty-states/empty-no-posts.png',
            'alt' => 'Aún no hay publicaciones',
            'width' => 512,
            'height' => 512,
        ],

        'empty-no-comments' => [
            'webp' => 'images/illustrations/empty-states/empty-no-comments.webp',
            'png' => 'images/illustrations/empty-states/empty-no-comments.png',
            'alt' => 'Sin comentarios todavía',
            'width' => 512,
            'height' => 512,
        ],

        'empty-no-notifications' => [
            'webp' => 'images/illustrations/empty-states/empty-no-notifications.webp',
            'png' => 'images/illustrations/empty-states/empty-no-notifications.png',
            'alt' => 'No hay notificaciones recientes',
            'width' => 512,
            'height' => 512,
        ],

        'empty-no-search-results' => [
            'webp' => 'images/illustrations/empty-states/empty-no-search-results.webp',
            'png' => 'images/illustrations/empty-states/empty-no-search-results.png',
            'alt' => 'No se encontraron publicaciones',
            'width' => 512,
            'height' => 512,
        ],

        'discovery-world-cuisine' => [
            'webp' => 'images/illustrations/discovery/discovery-world-cuisine.webp',
            'png' => 'images/illustrations/discovery/discovery-world-cuisine.png',
            'legacy' => 'images/image-hero.png',
            'alt' => 'Descubre cocinas y maridajes de todo el mundo',
            'width' => 1280,
            'height' => 720,
        ],

        'community-food-lovers' => [
            'webp' => 'images/illustrations/community/community-food-lovers.webp',
            'png' => 'images/illustrations/community/community-food-lovers.png',
            'legacy' => 'images/hero-gallery.png',
            'alt' => 'Comunidad de amantes de la gastronomía compartiendo experiencias',
            'width' => 1280,
            'height' => 720,
        ],

        'error-page-not-found' => [
            'webp' => 'images/illustrations/errors/error-page-not-found.webp',
            'png' => 'images/illustrations/errors/error-page-not-found.png',
            'legacy' => 'images/entre_sabores_error.png',
            'alt' => 'Ilustración de error',
            'width' => 512,
            'height' => 512,
        ],

        'onboarding-discover-flavors' => [
            'webp' => 'images/illustrations/onboarding/onboarding-discover-flavors.webp',
            'png' => 'images/illustrations/onboarding/onboarding-discover-flavors.png',
            'alt' => 'Descubre sabores de todo el mundo',
            'width' => 1280,
            'height' => 720,
        ],

        'onboarding-share-pairing' => [
            'webp' => 'images/illustrations/onboarding/onboarding-share-pairing.webp',
            'png' => 'images/illustrations/onboarding/onboarding-share-pairing.png',
            'alt' => 'Comparte tu maridaje con la comunidad',
            'width' => 1280,
            'height' => 720,
        ],

        'onboarding-explore-cultures' => [
            'webp' => 'images/illustrations/onboarding/onboarding-explore-cultures.webp',
            'png' => 'images/illustrations/onboarding/onboarding-explore-cultures.png',
            'alt' => 'Explora culturas y etiquetas gastronómicas',
            'width' => 1280,
            'height' => 720,
        ],

        'discovery-culinary-journey' => [
            'webp' => 'images/illustrations/discovery/discovery-culinary-journey.webp',
            'png' => 'images/illustrations/discovery/discovery-culinary-journey.png',
            'alt' => 'Un viaje gastronómico por culturas de todo el mundo',
            'width' => 1280,
            'height' => 720,
        ],

        'community-celebration-milestone' => [
            'webp' => 'images/illustrations/community/community-celebration-milestone.webp',
            'png' => 'images/illustrations/community/community-celebration-milestone.png',
            'alt' => 'Celebración de tu primer logro en la comunidad',
            'width' => 960,
            'height' => 720,
        ],

        'avatar-default-foodie' => [
            'webp' => 'images/avatars/avatar-default-foodie.webp',
            'png' => 'images/avatars/avatar-default-foodie.png',
            'legacy' => 'images/default.png',
            'alt' => 'Avatar predeterminado de Entre Sabores',
            'width' => 128,
            'height' => 128,
        ],

    ],

];
