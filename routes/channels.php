<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('user.{userId}', function (User $user, string $userId): bool {
    return (int) $user->id === (int) $userId;
});

/*
| Ejemplo futuro (no activo):
|
| use App\Models\Post;
|
| Broadcast::channel('post.{post}', function (?User $user, Post $post): bool {
|     return \Illuminate\Support\Facades\Gate::forUser($user)->allows('view', $post);
| });
|
*/
