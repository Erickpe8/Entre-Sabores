<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feed del muro — invitados (exploración global)
    |--------------------------------------------------------------------------
    |
    | Respuestas JSON cacheadas unos segundos para reducir carga cuando hay
    | mucho tráfico anónimo. Los usuarios autenticados no usan esta caché
    | (likes propios y datos sensibles al contexto).
    |
    | 0 = desactivado.
    |
    */

    'wall_guest_feed_ttl' => (int) env('WALL_GUEST_FEED_CACHE_TTL', 45),

];
