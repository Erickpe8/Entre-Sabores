<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Canales de broadcasting
|--------------------------------------------------------------------------
|
| PrivateChannel → Echo.private(...) → sesión + POST /broadcasting/auth
| Channel → Echo.channel(...) → público (sin auth en el canal)
|
| NOTA Seguridad — canal post.{id} (público):
| - Cualquiera que conozca el ID puede suscribirse y recibir eventos de ese post.
| - Coherente con posts 100 % públicos (misma superficie que GET /posts/{id}).
| - Si en el futuro hay posts privados / solo seguidores:
|     1) Registrar aquí un canal privado, p. ej. Broadcast::channel('post.{post}', …)
|     2) Autorizar con Gate::allows('view', $post) o lógica equivalente.
|     3) Sustituir Echo.channel por Echo.private en el detalle del post.
|     4) Los invitados solo podrían ver posts marcados como públicos (branch en la closure).
|
*/

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
