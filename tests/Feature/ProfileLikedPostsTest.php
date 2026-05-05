<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileLikedPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_profile_likes(): void
    {
        $this->getJson(route('profile.likes'))
            ->assertStatus(401);
    }

    public function test_user_receives_liked_posts_ordered_by_like_date(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $oldPost = Post::factory()->for($other)->create();
        $newPost = Post::factory()->for($other)->create();

        Like::query()->create([
            'user_id' => $user->id,
            'post_id' => $oldPost->id,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Like::query()->create([
            'user_id' => $user->id,
            'post_id' => $newPost->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson(route('profile.likes', ['per_page' => 10]))
            ->assertOk()
            ->assertJsonPath('posts.0.id', $newPost->id)
            ->assertJsonPath('posts.1.id', $oldPost->id)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_liked_posts_response_includes_post_resource_shape(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();

        Like::query()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->getJson(route('profile.likes'))
            ->assertOk()
            ->assertJsonStructure([
                'posts' => [
                    [
                        'id',
                        'title',
                        'likes_count',
                        'liked',
                        'user',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'has_more',
                    'total',
                ],
            ])
            ->assertJsonPath('posts.0.liked', true);
    }
}
