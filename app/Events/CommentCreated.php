<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dominio: se añadió un comentario a un post.
 *
 * @property-read array<string, mixed> $commentPayload Resource resuelto (CommentResource).
 */
class CommentCreated
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $commentPayload
     */
    public function __construct(
        public int $postId,
        public int $commentsCount,
        public array $commentPayload,
        public int $actorUserId,
    ) {}
}
