<?php

namespace App\Listeners\Broadcasting;

use App\Events\Broadcasting\CommentCreatedBroadcast;
use App\Events\CommentCreated;
use App\Support\OperationalLogger;
use App\Support\OperationalMetrics;

class SendCommentCreatedBroadcast
{
    public function handle(CommentCreated $event): void
    {
        if (config('broadcasting.default') === 'null') {
            return;
        }

        $started = microtime(true);

        broadcast(new CommentCreatedBroadcast(
            $event->postId,
            $event->commentsCount,
            $event->commentPayload,
            $event->actorUserId,
        ))->toOthers();

        OperationalLogger::broadcastEmitted('CommentCreatedBroadcast', [
            'post_id' => $event->postId,
            'comments_count' => $event->commentsCount,
            'comment_id' => $event->commentPayload['id'] ?? null,
        ], (microtime(true) - $started) * 1000);

        OperationalMetrics::incrementBroadcastsEmitted();
    }
}
