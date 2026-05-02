<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dominio: fila guardada en notifications para un usuario (tras incrementar contador).
 *
 * @param  array{title: string, body: string, url: string}  $payload
 */
class NotificationRecorded
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
}
