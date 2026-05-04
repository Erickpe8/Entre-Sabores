<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostReanalyzeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TagSeeder::class);
    }

    public function test_guest_cannot_reanalyze(): void
    {
        $post = Post::factory()->create();

        $this->postJson(route('posts.reanalyze', $post))
            ->assertUnauthorized();
    }

    public function test_non_owner_cannot_reanalyze(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->postJson(route('posts.reanalyze', $post))
            ->assertForbidden();
    }

    public function test_owner_can_queue_reanalyze_and_clears_ai_analysis(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $owner->id,
            'ai_analysis' => [
                'historia' => 'x',
                'afinidad' => 'x',
                'equilibrio' => 'x',
                'recomendacion' => 'x',
                'score' => 7,
            ],
        ]);

        $this->actingAs($owner)
            ->postJson(route('posts.reanalyze', $post))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $post->refresh();
        $this->assertNull($post->ai_analysis);
    }
}
