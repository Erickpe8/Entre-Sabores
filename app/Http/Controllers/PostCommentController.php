<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\JsonResponse;

class PostCommentController extends Controller
{
    public function store(StorePostCommentRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $comment->load('user:id,first_name,last_name,username,profile_photo');

        $actor = $request->user();

        if ($post->user_id !== $actor->id) {
            $post->user->notify(new NewCommentNotification($post, $comment, $actor, 'on_post'));
        }

        if (! empty($validated['parent_id'])) {
            $parent = Comment::query()->find($validated['parent_id']);
            if (
                $parent !== null
                && $parent->user_id !== $actor->id
                && $parent->user_id !== $post->user_id
            ) {
                $parent->user->notify(new NewCommentNotification($post, $comment, $actor, 'reply'));
            }
        }

        return response()->json([
            'comment' => (new CommentResource($comment))->resolve(),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }
}
