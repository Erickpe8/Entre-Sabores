<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewCommentNotification extends Notification
{
    use Queueable;

    /**
     * @param  'on_post'|'reply'  $kind
     */
    public function __construct(
        public Post $post,
        public Comment $comment,
        public User $actor,
        public string $kind,
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
        $preview = Str::limit(trim((string) $this->comment->body), 120);

        $title = $this->kind === 'reply'
            ? $this->actor->first_name.' respondió tu comentario'
            : $this->actor->first_name.' comentó en tu publicación';

        return [
            'event' => $this->kind === 'reply' ? 'comment_reply' : 'comment',
            'kind' => $this->kind,
            'title' => $title,
            'body' => $preview,
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
            'actor_id' => $this->actor->id,
            'actor_username' => $this->actor->username,
            'actor_name' => trim($this->actor->first_name.' '.$this->actor->last_name),
            'actor_avatar' => $this->actor->profile_photo_url,
            'url' => route('posts.show', $this->post),
        ];
    }
}
