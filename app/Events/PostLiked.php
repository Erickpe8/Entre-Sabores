<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dominio: el conteo de likes de un post cambió (HTTP ya respondió; WS es aparte).
 */
class PostLiked
{
    use Dispatchable;

    public function __construct(
        public int $postId,
        public int $likesCount,
        public int $actorUserId,
    ) {}
}
