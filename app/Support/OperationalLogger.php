<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Eventos de negocio / seguridad en canal structured (JSON por línea).
 */
final class OperationalLogger
{
    public static function postCreated(Post $post, Request $request, int $tagCount): void
    {
        Log::channel('structured')->info('post.created', [
            'post_id' => $post->id,
            'user_id' => $post->user_id,
            'has_image' => $post->image_path !== null,
            'tag_count' => $tagCount,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);
    }

    public static function authLogin(int|string $userId, Request $request, bool $remember): void
    {
        Log::channel('structured')->info('auth.login', [
            'user_id' => $userId,
            'remember' => $remember,
            'ip' => $request->ip(),
        ]);
    }

    public static function authLogout(int|string $userId, Request $request): void
    {
        Log::channel('structured')->info('auth.logout', [
            'user_id' => $userId,
            'ip' => $request->ip(),
        ]);
    }

    public static function authFailed(?string $email, Request $request): void
    {
        Log::channel('structured')->warning('auth.failed', [
            'email_hash' => $email !== null ? hash('sha256', strtolower($email)) : null,
            'ip' => $request->ip(),
        ]);
    }
}
