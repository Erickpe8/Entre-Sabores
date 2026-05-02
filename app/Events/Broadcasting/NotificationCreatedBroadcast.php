<?php

namespace App\Events\Broadcasting;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Payload WS: notificación nueva para user.{id}.
 */
class NotificationCreatedBroadcast implements ShouldBroadcast
{
    use Dispatchable;

    /**
     * @param  array{title: string, body: string, url: string}  $payload
     */
    public function __construct(
        public int $userId,
        public string $notificationId,
        public array $payload,
        public int $unreadCount,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notificationId,
            'title' => $this->payload['title'] ?? 'Actividad',
            'body' => $this->payload['body'] ?? '',
            'url' => $this->payload['url'] ?? '#',
            'unread_count' => $this->unreadCount,
        ];
    }
}
