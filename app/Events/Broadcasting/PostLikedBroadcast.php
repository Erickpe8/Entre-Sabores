<?php

namespace App\Events\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Payload WS: me gusta actualizado en un post (canal público post.{id}).
 */
class PostLikedBroadcast implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public int $postId,
        public int $likesCount,
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
        return 'post.like.updated';
    }

    /**
     * @return array<string, int>
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'likes_count' => $this->likesCount,
            'actor_user_id' => $this->actorUserId,
        ];
    }
}
