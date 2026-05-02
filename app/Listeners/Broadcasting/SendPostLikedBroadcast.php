<?php

namespace App\Listeners\Broadcasting;

use App\Events\Broadcasting\PostLikedBroadcast;
use App\Events\PostLiked;
use App\Support\OperationalLogger;
use App\Support\OperationalMetrics;

class SendPostLikedBroadcast
{
    public function handle(PostLiked $event): void
    {
        if (config('broadcasting.default') === 'null') {
            return;
        }

        $started = microtime(true);

        broadcast(new PostLikedBroadcast(
            $event->postId,
            $event->likesCount,
            $event->actorUserId,
        ))->toOthers();

        OperationalLogger::broadcastEmitted('PostLikedBroadcast', [
            'post_id' => $event->postId,
            'likes_count' => $event->likesCount,
        ], (microtime(true) - $started) * 1000);

        OperationalMetrics::incrementBroadcastsEmitted();
    }
}
