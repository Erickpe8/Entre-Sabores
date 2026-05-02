<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Http\Requests\StorePostCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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
        $notifiedUserIds = [];

        if ($post->user_id !== $actor->id) {
            $post->user->notify(new NewCommentNotification($post, $comment, $actor, 'on_post'));
            $notifiedUserIds[] = (int) $post->user_id;
        }

        if (! empty($validated['parent_id'])) {
            $parent = Comment::query()->find($validated['parent_id']);
            if (
                $parent !== null
                && $parent->user_id !== $actor->id
                && $parent->user_id !== $post->user_id
            ) {
                $parent->user->notify(new NewCommentNotification($post, $comment, $actor, 'reply'));
                $notifiedUserIds[] = (int) $parent->user_id;
            }
        }

        $this->notifyMentionedUsers(
            post: $post,
            comment: $comment,
            actor: $actor,
            alreadyNotifiedUserIds: $notifiedUserIds,
        );

        CommentCreated::dispatch(
            $post->id,
            $post->comments()->count(),
            (new CommentResource($comment))->resolve(request()),
            $actor->id,
        );

        return response()->json([
            'comment' => (new CommentResource($comment))->resolve(),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    /**
     * Notifica menciones @usuario evitando duplicados y auto-menciones.
     *
     * @param  array<int, int>  $alreadyNotifiedUserIds
     */
    private function notifyMentionedUsers(Post $post, Comment $comment, User $actor, array $alreadyNotifiedUserIds = []): void
    {
        preg_match_all('/(?<![A-Za-z0-9_])@([a-z0-9_-]{3,30})/i', (string) $comment->body, $matches);
        $handles = collect($matches[1] ?? [])
            ->map(fn (string $h) => strtolower(trim($h)))
            ->filter()
            ->unique()
            ->values();

        if ($handles->isEmpty()) {
            return;
        }

        User::query()
            ->whereIn('username', $handles->all())
            ->where('id', '!=', $actor->id)
            ->whereNotIn('id', array_values(array_unique($alreadyNotifiedUserIds)))
            ->get(['id', 'username', 'first_name', 'last_name', 'profile_photo'])
            ->each(fn (User $mentioned) => $mentioned->notify(
                new NewCommentNotification($post, $comment, $actor, 'mention')
            ));
    }
}
