<?php

namespace App\Jobs;

use App\Events\Broadcasting\PostAnalysisGeneratedBroadcast;
use App\Models\Post;
use App\Services\MaridajeAiAnalysisService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeneratePostAnalysisJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 1;

    public int $uniqueFor = 86400;

    public function __construct(
        public int $postId,
    ) {}

    public function uniqueId(): string
    {
        return 'maridaje-analysis-post-'.$this->postId;
    }

    public function handle(MaridajeAiAnalysisService $analysisService): void
    {
        $post = Post::query()->find($this->postId);
        if ($post === null) {
            return;
        }

        if ($post->ai_analysis !== null) {
            return;
        }

        try {
            $result = $analysisService->analyzeDescription($post->description);
            if ($result === null) {
                return;
            }

            $post->forceFill(['ai_analysis' => $result])->save();

            if (config('broadcasting.default') !== 'null') {
                broadcast(new PostAnalysisGeneratedBroadcast($post->id, $result));
            }
        } catch (Throwable $e) {
            Log::warning('GeneratePostAnalysisJob: error al guardar análisis.', [
                'post_id' => $this->postId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
