<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WallSocialFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TagSeeder::class);
    }

    public function test_guest_cannot_toggle_like(): void
    {
        $post = Post::factory()->create();

        $this->postJson(route('posts.likes.toggle', $post))
            ->assertStatus(401);
    }

    public function test_authenticated_user_can_toggle_like_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('posts.likes.toggle', $post))
            ->assertOk()
            ->assertJson([
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->actingAs($user)
            ->postJson(route('posts.likes.toggle', $post))
            ->assertOk()
            ->assertJson([
                'liked' => false,
                'likes_count' => 0,
            ]);
    }

    public function test_guest_cannot_follow(): void
    {
        $target = User::factory()->create(['username' => 'target_user']);

        $this->postJson(route('users.follow.store', ['username' => 'target_user']))
            ->assertStatus(401);
    }

    public function test_user_can_follow_and_unfollow(): void
    {
        $viewer = User::factory()->create(['username' => 'viewer']);
        $target = User::factory()->create(['username' => 'creator']);

        $this->actingAs($viewer)
            ->postJson(route('users.follow.store', ['username' => 'creator']))
            ->assertOk()
            ->assertJson([
                'following' => true,
                'followers_count' => 1,
            ]);

        $this->actingAs($viewer)
            ->deleteJson(route('users.follow.destroy', ['username' => 'creator']))
            ->assertOk()
            ->assertJson([
                'following' => false,
                'followers_count' => 0,
            ]);
    }

    public function test_user_cannot_follow_self(): void
    {
        $user = User::factory()->create(['username' => 'solo']);

        $this->actingAs($user)
            ->postJson(route('users.follow.store', ['username' => 'solo']))
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'No puedes seguirte a ti mismo.']);
    }

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('posts.comments.store', $post), [
                'body' => 'Un comentario de prueba',
            ]);

        $response->assertCreated()
            ->assertJsonPath('comment.body', 'Un comentario de prueba')
            ->assertJsonPath('comments_count', 1);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Un comentario de prueba',
            'parent_id' => null,
        ]);
    }

    public function test_authenticated_user_can_reply_to_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $parent = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Comentario raíz',
        ]);

        $this->actingAs($user)
            ->postJson(route('posts.comments.store', $post), [
                'body' => 'Respuesta',
                'parent_id' => $parent->id,
            ])
            ->assertCreated()
            ->assertJsonPath('comment.body', 'Respuesta');

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'body' => 'Respuesta',
        ]);
    }

    public function test_feed_filter_returns_posts_and_meta_for_guest(): void
    {
        Post::factory()->count(2)->create();

        $this->getJson(route('posts.filter'))
            ->assertOk()
            ->assertJsonStructure([
                'posts',
                'meta' => [
                    'total_posts',
                    'limit',
                    'current_page',
                    'has_more',
                    'feed_mode',
                ],
            ]);
    }

    public function test_feed_filter_following_as_guest_returns_empty_placeholder(): void
    {
        $this->getJson(route('posts.filter', ['following' => true]))
            ->assertOk()
            ->assertJsonPath('meta.feed_mode', 'following_guest')
            ->assertJsonPath('meta.guest_following', true)
            ->assertJsonPath('posts', []);
    }

    public function test_feed_filter_supports_tag_ids(): void
    {
        $tag = Tag::query()->where('type', Tag::TYPE_COUNTRY)->firstOrFail();
        Post::factory()->create();

        $this->getJson(route('posts.filter', ['tag_ids' => [$tag->id]]))
            ->assertOk()
            ->assertJsonStructure(['posts', 'meta']);
    }

    public function test_feed_filter_sort_popular(): void
    {
        Post::factory()->create();

        $this->getJson(route('posts.filter', ['sort' => 'popular']))
            ->assertOk()
            ->assertJsonPath('meta.feed_mode', 'explore_popular');
    }

    public function test_feed_filter_search_matches_related_tag_name(): void
    {
        $colombia = Tag::query()->where('name', 'Colombia')->firstOrFail();
        $notColombia = Tag::query()->where('id', '!=', $colombia->id)->firstOrFail();

        $matching = Post::factory()->create([
            'title' => 'Publicación sin texto Colombia',
            'description' => 'Descripción única sin el término.',
        ]);
        $matching->tags()->sync([$colombia->id]);

        $other = Post::factory()->create([
            'title' => 'Otra sin match de búsqueda',
            'description' => 'Solo ruido.',
        ]);
        $other->tags()->sync([$notColombia->id]);

        $res = $this->getJson(route('posts.filter', ['search' => 'Colombia']));

        $res->assertOk();
        $ids = collect($res->json('posts'))->pluck('id')->all();
        $this->assertContains($matching->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }
}
