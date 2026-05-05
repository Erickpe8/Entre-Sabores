<?php

namespace App\Events\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class PostModerationUpdatedBroadcast implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public int $postId,
        public int $userId,
        public string $status,
        public string $analysisStatus,
        public bool $flagged,
        public string $summary,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('posts.moderation')];
    }

    public function broadcastAs(): string
    {
        return 'post.moderation.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'user_id' => $this->userId,
            'status' => $this->status,
            'analysis_status' => $this->analysisStatus,
            'flagged' => $this->flagged,
            'summary' => $this->summary,
        ];
    }
}

