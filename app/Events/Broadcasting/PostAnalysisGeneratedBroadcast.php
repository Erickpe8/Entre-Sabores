<?php

namespace App\Events\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Payload WS: análisis IA listo para un post (canal público post.{id}).
 *
 * Nombre de dominio solicitado: PostAnalysisGenerated → mismo contrato que otros *Broadcast.
 */
class PostAnalysisGeneratedBroadcast implements ShouldBroadcast
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $aiAnalysis
     */
    public function __construct(
        public int $postId,
        public array $aiAnalysis,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('post.'.$this->postId)];
    }

    public function broadcastAs(): string
    {
        return 'post.analysis.generated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'ai_analysis' => $this->aiAnalysis,
        ];
    }
}
