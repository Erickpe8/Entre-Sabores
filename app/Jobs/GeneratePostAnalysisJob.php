<?php

namespace App\Jobs;

use App\Events\Broadcasting\PostAnalysisGeneratedBroadcast;
use App\Models\Post;
use App\Services\MaridajeAiAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeneratePostAnalysisJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function __construct(
        public int $postId,
    ) {}

    public function handle(MaridajeAiAnalysisService $analysisService): void
    {
        Log::info('maridaje.job.start', [
            'post_id' => $this->postId,
            'job' => static::class,
        ]);

        try {
            $post = Post::query()->findOrFail($this->postId);
        } catch (ModelNotFoundException $e) {
            Log::error('maridaje.job.post_not_found', [
                'post_id' => $this->postId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        Log::info('maridaje.job.post_loaded', [
            'post_id' => $post->id,
            'ai_analysis_is_null' => $post->ai_analysis === null,
        ]);

        // CASO A: ya hay análisis (idempotencia / carrera)
        if ($post->ai_analysis !== null) {
            Log::info('maridaje.job.skip_existing_analysis', [
                'post_id' => $post->id,
            ]);

            return;
        }

        $result = null;
        $serviceException = null;

        try {
            $result = $analysisService->analyzeDescription($post->description);
        } catch (Throwable $e) {
            $serviceException = $e;
            Log::error('maridaje.job.service_exception', [
                'post_id' => $post->id,
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);
        }

        Log::info('AI ANALYSIS RESULT', [
            'post_id' => $this->postId,
            'is_null' => $result === null,
            'has_service_exception' => $serviceException !== null,
            'result_keys' => is_array($result) ? array_keys($result) : [],
        ]);

        if ($serviceException !== null) {
            $this->persistFallback($post, 'service_exception', $serviceException->getMessage());

            return;
        }

        if ($result === null) {
            Log::warning('maridaje.job.service_returned_null', [
                'post_id' => $post->id,
                'hint' => 'Revisar MARIDAJE_AI_*, config:cache, longitud de descripción o respuesta HTTP.',
            ]);
            $this->persistFallback($post, 'service_null');

            return;
        }

        if (! $this->isValidAnalysisPayload($result)) {
            Log::error('maridaje.job.invalid_payload', [
                'post_id' => $post->id,
                'keys' => array_keys($result),
            ]);
            $this->persistFallback($post, 'invalid_payload');

            return;
        }

        // Resultado válido del proveedor
        $this->persistAnalysis($post, $result, 'api');
    }

    /**
     * Persistencia obligatoria del fallback cuando no hay análisis válido.
     */
    private function persistFallback(Post $post, string $reason, ?string $detail = null): void
    {
        $payload = $this->fallbackPayload();
        Log::warning('maridaje.job applying_fallback', [
            'post_id' => $post->id,
            'reason' => $reason,
            'detail' => $detail,
        ]);
        $this->persistAnalysis($post, $payload, 'fallback', $reason);
    }

    /**
     * @return array{historia: string, afinidad: null, equilibrio: null, recomendacion: string, score: int}
     */
    private function fallbackPayload(): array
    {
        return [
            'historia' => 'No se pudo generar el análisis en este momento.',
            'afinidad' => null,
            'equilibrio' => null,
            'recomendacion' => 'Intenta nuevamente.',
            'score' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $analysis
     */
    private function persistAnalysis(Post $post, array $analysis, string $source, ?string $fallbackReason = null): void
    {
        $post->forceFill([
            'ai_analysis' => $analysis,
        ]);

        $saved = $post->save();

        if (! $saved) {
            Log::error('maridaje.job.save_failed_false', [
                'post_id' => $post->id,
                'source' => $source,
            ]);

            throw new \RuntimeException('GeneratePostAnalysisJob: save() devolvió false.');
        }

        $post->refresh();

        if ($post->ai_analysis === null) {
            Log::error('maridaje.job.save_verify_null', [
                'post_id' => $post->id,
                'source' => $source,
            ]);

            throw new \RuntimeException('ai_analysis no persistió tras save(); revisar modelo y migración.');
        }

        Log::info('maridaje.job.saved', [
            'post_id' => $post->id,
            'source' => $source,
            'fallback_reason' => $fallbackReason,
            'score' => $analysis['score'] ?? null,
        ]);

        if (config('broadcasting.default') !== 'null') {
            try {
                broadcast(new PostAnalysisGeneratedBroadcast($post->id, $analysis));
            } catch (Throwable $broadcastException) {
                Log::error('maridaje.job.broadcast_failed', [
                    'post_id' => $post->id,
                    'message' => $broadcastException->getMessage(),
                    'exception' => $broadcastException::class,
                ]);
            }
        }
    }

    /**
     * Valida respuesta normalizada del proveedor (no aplica al fallback).
     *
     * @param  array<string, mixed>  $result
     */
    private function isValidAnalysisPayload(array $result): bool
    {
        $required = ['historia', 'afinidad', 'equilibrio', 'recomendacion', 'score'];

        foreach ($required as $key) {
            if (! array_key_exists($key, $result)) {
                return false;
            }
        }

        foreach (['historia', 'afinidad', 'equilibrio', 'recomendacion'] as $stringKey) {
            if (! is_string($result[$stringKey])) {
                return false;
            }
        }

        if (! is_int($result['score']) && ! is_float($result['score']) && ! is_numeric($result['score'])) {
            return false;
        }

        return true;
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('maridaje.job.failed_permanent', [
            'post_id' => $this->postId,
            'message' => $exception?->getMessage(),
            'exception' => $exception !== null ? $exception::class : null,
        ]);
    }
}
