<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePostCountryRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function show(Post $post): JsonResponse
    {
        $post->load([
            'user:id,first_name,last_name,username,profile_photo',
            'country:id,name,slug,flag_emoji',
        ]);

        $comments = $post->comments()->with('user:id,first_name,last_name,username,profile_photo')->latest()->get()->map(fn ($c) => [
            'id' => $c->id,
            'body' => $c->body,
            'created_at' => $c->created_at?->toIso8601String(),
            'user' => [
                'id' => $c->user->id,
                'name' => trim($c->user->first_name.' '.$c->user->last_name),
                'username' => $c->user->username,
                'avatar' => $c->user->profile_photo_url,
            ],
        ]);

        $user = $post->user;

        return response()->json([
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'story' => $post->story,
                'food_label' => $post->food_label,
                'drink_label' => $post->drink_label,
                'experience_type' => $post->experience_type,
                'drink_type' => $post->drink_type,
                'comments_count' => $post->comments()->count(),
                'created_at' => $post->created_at?->toIso8601String(),
                'country_id' => $post->country_id,
                'country' => $post->country ? [
                    'id' => $post->country->id,
                    'name' => $post->country->name,
                    'slug' => $post->country->slug,
                    'flag_emoji' => $post->country->flag_emoji,
                ] : null,
                'user' => [
                    'id' => $user->id,
                    'name' => trim($user->first_name.' '.$user->last_name),
                    'username' => $user->username,
                    'avatar' => $user->profile_photo_url,
                    'profile_url' => route('user.profile', ['username' => $user->username]),
                ],
                'comments' => $comments,
            ],
        ]);
    }

    public function updateCountry(UpdatePostCountryRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->update([
            'country_id' => $request->integer('country_id'),
        ]);

        return response()->json([
            'ok' => true,
            'country_id' => $post->country_id,
        ]);
    }
}
