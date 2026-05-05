<?php

namespace App\Jobs;

use App\Events\Broadcasting\PostModerationUpdatedBroadcast;
use App\Models\Post;
use App\Notifications\PostRejectedNotification;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzePostJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [10, 30, 90];
    }

    public function __construct(
        public int $postId,
    ) {}

    public function handle(AIService $aiService): void
    {
        $post = Post::query()->find($this->postId);
        if ($post === null) {
            throw new ModelNotFoundException("Post {$this->postId} no encontrado.");
        }

        $post->forceFill([
            'analysis_status' => Post::ANALYSIS_STATUS_PROCESSING,
        ])->save();

        $result = $aiService->analyzePostForModeration($post);
        Log::info('moderation.ai.result', [
            'post_id' => $post->id,
            'flagged' => (bool) ($result['flagged'] ?? false),
            'reasons' => $result['reasons'] ?? [],
            'confidence' => $result['confidence'] ?? null,
        ]);

        DB::transaction(function () use ($post, $result): void {
            $post->refresh();

            $analysisStatus = Post::ANALYSIS_STATUS_COMPLETED;
            $status = Post::STATUS_ACTIVE;
            $moderationReason = null;

            if ((bool) ($result['flagged'] ?? false)) {
                $status = Post::STATUS_REJECTED;
                $moderationReason = $result['reasons'] ?? null;
            }

            $post->forceFill([
                'status' => $status,
                'analysis_status' => $analysisStatus,
                'analysis_result' => $result,
                'moderation_reason' => $moderationReason,
            ])->save();

            if ($status === Post::STATUS_REJECTED) {
                $post->user?->notify(new PostRejectedNotification($post, $result));
                $post->delete();
            }
        });

        event(new PostModerationUpdatedBroadcast(
            postId: $post->id,
            userId: $post->user_id,
            status: $post->status,
            analysisStatus: $post->analysis_status,
            flagged: (bool) ($result['flagged'] ?? false),
            summary: (string) ($result['summary'] ?? ''),
        ));
    }

    public function failed(?Throwable $exception): void
    {
        $post = Post::query()->withTrashed()->find($this->postId);
        if ($post !== null) {
            $post->forceFill([
                'analysis_status' => Post::ANALYSIS_STATUS_FAILED,
                'analysis_result' => [
                    'flagged' => false,
                    'reasons' => [
                        'profanity' => false,
                        'irrelevant_content' => false,
                        'prompt_injection' => false,
                    ],
                    'confidence' => 0,
                    'summary' => 'No se pudo completar el análisis automático.',
                    'error' => $exception?->getMessage(),
                ],
            ])->save();
        }

        Log::error('moderation.job.failed', [
            'post_id' => $this->postId,
            'message' => $exception?->getMessage(),
            'exception' => $exception !== null ? $exception::class : null,
        ]);
    }
}

