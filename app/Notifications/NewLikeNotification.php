<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLikeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $liker,
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
            'event' => 'like',
            'title' => $this->liker->first_name.' te dio me gusta',
            'body' => $this->post->title,
            'post_id' => $this->post->id,
            'actor_id' => $this->liker->id,
            'actor_username' => $this->liker->username,
            'actor_name' => trim($this->liker->first_name.' '.$this->liker->last_name),
            'actor_avatar' => $this->liker->profile_photo_url,
            'url' => route('posts.show', $this->post),
        ];
    }
}
