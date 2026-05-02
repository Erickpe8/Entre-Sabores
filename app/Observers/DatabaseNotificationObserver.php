<?php

namespace App\Observers;

use App\Events\NotificationRecorded;
use App\Models\User;
use App\Support\NotificationApiPayload;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification): void
    {
        if ($notification->notifiable_type !== User::class) {
            return;
        }

        $user = $notification->notifiable;
        if (! $user instanceof User) {
            return;
        }

        $user->increment('unread_notifications_count');
        $user->refresh();

        $safe = NotificationApiPayload::forApi($notification->data);

        NotificationRecorded::dispatch(
            userId: $user->id,
            notificationId: $notification->id,
            payload: [
                'title' => (string) ($safe['title'] ?? 'Actividad'),
                'body' => (string) ($safe['body'] ?? ''),
                'url' => (string) ($safe['url'] ?? '#'),
            ],
            unreadCount: (int) $user->unread_notifications_count,
        );
    }
}
