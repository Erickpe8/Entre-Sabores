<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostCommentRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostCommentController extends Controller
{
    public function store(StorePostCommentRequest $request, Post $post): JsonResponse
    {
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->input('body'),
        ]);

        $comment->load('user:id,first_name,last_name,username,profile_photo');

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at?->toIso8601String(),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => trim($comment->user->first_name.' '.$comment->user->last_name),
                    'username' => $comment->user->username,
                    'avatar' => $comment->user->profile_photo_url,
                ],
            ],
            'comments_count' => $post->comments()->count(),
        ], 201);
    }
}
