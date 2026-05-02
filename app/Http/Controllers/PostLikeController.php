<?php

namespace App\Http\Controllers;

use App\Events\PostLiked;
use App\Models\Post;
use App\Notifications\NewLikeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    /**
     * Alterna like del usuario autenticado en el post (sin duplicados).
     */
    public function toggle(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $deleted = $post->likes()->where('user_id', $user->id)->delete();

        if ($deleted === 0) {
            $post->likes()->create(['user_id' => $user->id]);
            $liked = true;

            if ($post->user_id !== $user->id) {
                $post->user->notify(new NewLikeNotification($post, $user));
            }
        } else {
            $liked = false;
        }

        $likesCount = $post->likes()->count();

        PostLiked::dispatch($post->id, $likesCount, $user->id);

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }
}
