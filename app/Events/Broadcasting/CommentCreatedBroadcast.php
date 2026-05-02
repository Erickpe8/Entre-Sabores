<?php

namespace App\Events\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Payload WS: nuevo comentario en post.{id}.
 */
class CommentCreatedBroadcast implements ShouldBroadcast
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $comment
     */
    public function __construct(
        public int $postId,
        public int $commentsCount,
        public array $comment,
        public int $actorUserId,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('post.'.$this->postId)];
    }

    public function broadcastAs(): string
    {
        return 'post.comment.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'comments_count' => $this->commentsCount,
            'comment' => $this->comment,
            'actor_user_id' => $this->actorUserId,
        ];
    }
}
