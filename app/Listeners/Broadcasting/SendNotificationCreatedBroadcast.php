<?php

namespace App\Listeners\Broadcasting;

use App\Events\Broadcasting\NotificationCreatedBroadcast;
use App\Events\NotificationRecorded;
use App\Support\OperationalLogger;
use App\Support\OperationalMetrics;

class SendNotificationCreatedBroadcast
{
    public function handle(NotificationRecorded $event): void
    {
        if (config('broadcasting.default') === 'null') {
            return;
        }

        $started = microtime(true);

        broadcast(new NotificationCreatedBroadcast(
            $event->userId,
            $event->notificationId,
            $event->payload,
            $event->unreadCount,
        ));

        OperationalLogger::broadcastEmitted('NotificationCreatedBroadcast', [
            'user_id' => $event->userId,
            'notification_id' => $event->notificationId,
        ], (microtime(true) - $started) * 1000);

        OperationalMetrics::incrementBroadcastsEmitted();
    }
}
