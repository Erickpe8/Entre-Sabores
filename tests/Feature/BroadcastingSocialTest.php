<?php

namespace Tests\Feature;

use App\Events\CommentCreated;
use App\Events\NotificationRecorded;
use App\Events\PostLiked;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Integración: los controladores / observer disparan eventos de dominio (sin acoplarse al WS).
 */
class BroadcastingSocialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TagSeeder::class);
    }

    public function test_like_dispatches_post_liked_domain_event(): void
    {
        Event::fake([PostLiked::class]);

        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('posts.likes.toggle', $post))
            ->assertOk();

        Event::assertDispatched(PostLiked::class, function (PostLiked $event) use ($post, $user): bool {
            return $event->postId === $post->id
                && $event->likesCount === 1
                && $event->actorUserId === $user->id;
        });
    }

    public function test_comment_dispatches_comment_created_domain_event(): void
    {
        Event::fake([CommentCreated::class]);

        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('posts.comments.store', $post), [
                'body' => 'Hola en vivo',
            ])
            ->assertCreated();

        Event::assertDispatched(CommentCreated::class, function (CommentCreated $event) use ($post, $user): bool {
            return $event->postId === $post->id
                && $event->commentsCount === 1
                && $event->actorUserId === $user->id
                && ($event->commentPayload['body'] ?? null) === 'Hola en vivo';
        });
    }

    public function test_like_on_foreign_post_dispatches_notification_recorded_for_author(): void
    {
        Event::fake([NotificationRecorded::class]);

        $author = User::factory()->create();
        $liker = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->actingAs($liker)
            ->postJson(route('posts.likes.toggle', $post))
            ->assertOk();

        Event::assertDispatched(NotificationRecorded::class, function (NotificationRecorded $event) use ($author): bool {
            return $event->userId === $author->id
                && $event->unreadCount >= 1;
        });
    }
}
