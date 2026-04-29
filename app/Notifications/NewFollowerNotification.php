<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $follower,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => 'follow',
            'title' => $this->follower->first_name.' empezó a seguirte',
            'body' => '@'.$this->follower->username,
            'actor_id' => $this->follower->id,
            'actor_username' => $this->follower->username,
            'actor_name' => trim($this->follower->first_name.' '.$this->follower->last_name),
            'actor_avatar' => $this->follower->profile_photo_url,
            'url' => route('profile.show', ['username' => $this->follower->username]),
        ];
    }
}
