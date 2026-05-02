<?php

namespace Tests\Unit;

use App\Events\Broadcasting\CommentCreatedBroadcast;
use App\Events\Broadcasting\NotificationCreatedBroadcast;
use App\Events\Broadcasting\PostLikedBroadcast;
use PHPUnit\Framework\TestCase;

/**
 * Contrato estable del JSON enviado por WS (sin depender del facade de broadcasting).
 */
class BroadcastingPayloadTest extends TestCase
{
    public function test_post_liked_broadcast_shape(): void
    {
        $e = new PostLikedBroadcast(10, 4, 2);

        $this->assertSame('post.10', $e->broadcastOn()[0]->name);
        $this->assertSame('post.like.updated', $e->broadcastAs());
        $this->assertSame(
            [
                'post_id' => 10,
                'likes_count' => 4,
                'actor_user_id' => 2,
            ],
            $e->broadcastWith(),
        );
    }

    public function test_comment_created_broadcast_shape(): void
    {
        $comment = ['id' => 'a', 'body' => 'x'];
        $e = new CommentCreatedBroadcast(3, 7, $comment, 9);

        $this->assertSame('post.3', $e->broadcastOn()[0]->name);
        $this->assertSame('post.comment.created', $e->broadcastAs());
        $this->assertSame(7, $e->broadcastWith()['comments_count']);
        $this->assertSame($comment, $e->broadcastWith()['comment']);
    }

    public function test_notification_created_broadcast_shape(): void
    {
        $e = new NotificationCreatedBroadcast(1, 'nid', [
            'title' => 'T',
            'body' => 'B',
            'url' => '/p',
        ], 5);

        $this->assertSame('private-user.1', $e->broadcastOn()[0]->name);
        $this->assertSame('notification.created', $e->broadcastAs());
        $with = $e->broadcastWith();
        $this->assertSame('nid', $with['id']);
        $this->assertSame(5, $with['unread_count']);
    }
}
