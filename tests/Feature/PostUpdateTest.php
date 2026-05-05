<?php

namespace Tests\Feature;

use App\Jobs\GeneratePostAnalysisJob;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PostUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_post(): void
    {
        $post = Post::factory()->create();

        $this->patchJson(route('posts.update', $post), [
            'title' => 'Post editado',
            'description' => 'Texto editado',
        ])->assertUnauthorized();
    }

    public function test_user_cannot_update_post_from_other_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->patchJson(route('posts.update', $post), [
                'title' => 'Post editado',
                'description' => 'Texto editado',
            ])->assertForbidden();
    }

    public function test_owner_updates_post_and_reanalysis_is_queued(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Título inicial',
            'description' => 'Texto original',
            'content' => 'Texto original',
            'food' => 'Tacos',
            'drink' => 'Horchata',
            'analysis_status' => Post::ANALYSIS_STATUS_COMPLETED,
            'analysis_result' => ['score' => 8],
            'ai_analysis' => ['score' => 8],
        ]);

        $this->actingAs($owner)
            ->patchJson(route('posts.update', $post), [
                'title' => 'Título actualizado',
                'description' => 'Texto actualizado',
                'food' => 'Arepa',
                'drink' => 'Café',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('post.analysis_status', Post::ANALYSIS_STATUS_PENDING);

        $post->refresh();
        $this->assertSame('Título actualizado', $post->title);
        $this->assertSame('Texto actualizado', $post->description);
        $this->assertSame('Texto actualizado', $post->content);
        $this->assertSame('Arepa', $post->food);
        $this->assertSame('Café', $post->drink);
        $this->assertSame(Post::ANALYSIS_STATUS_PENDING, $post->analysis_status);
        $this->assertNull($post->analysis_result);
        $this->assertNull($post->ai_analysis);

        Queue::assertPushed(GeneratePostAnalysisJob::class, function (GeneratePostAnalysisJob $job) use ($post): bool {
            return $job->postId === $post->id;
        });
    }
}

