<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function store(StorePostRequest $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = $request->user()->posts()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
        ]);

        $post->tags()->sync(array_values(array_unique($validated['tags'])));

        $post->load([
            'user:id,first_name,last_name,username,profile_photo',
            'tags' => fn ($q) => $q->orderBy('type')->orderBy('sort_order'),
        ]);
        $post->loadCount(['comments', 'likes']);

        return response()->json([
            'post' => (new PostResource($post))->resolve(),
        ], 201);
    }

    public function show(Request $request, Post $post): JsonResponse|View
    {
        $post = $this->hydratePostForShow($post);

        if ($request->expectsJson()) {
            return response()->json([
                'post' => (new PostResource($post))->resolve(),
            ]);
        }

        $payload = (new PostResource($post))->resolve();

        return view('posts.show', [
            'post' => $post,
            'postPayload' => $payload,
            'pageTitle' => $post->title.' — '.config('app.name'),
            'metaDescription' => $payload['excerpt'] !== '' ? $payload['excerpt'] : $post->title,
            'ogImage' => $post->image_url ? url($post->image_url) : null,
            'ogUrl' => route('posts.show', $post),
            'postShowConfig' => [
                'postBaseUrl' => '/posts',
                'commentStoreUrl' => route('posts.comments.store', ['post' => $post], false),
                'loginUrl' => route('login', [], false),
                'isAuthenticated' => auth()->check(),
            ],
        ]);
    }

    private function hydratePostForShow(Post $post): Post
    {
        $post->load([
            'user:id,first_name,last_name,username,profile_photo',
            'tags' => fn ($q) => $q->orderBy('type')->orderBy('sort_order'),
            'comments' => fn ($q) => $q->with('user:id,first_name,last_name,username,profile_photo')
                ->orderBy('created_at'),
        ]);
        $post->loadCount(['comments', 'likes']);

        if (auth()->check()) {
            $post->setAttribute(
                'liked_by_me',
                $post->likes()->where('user_id', auth()->id())->exists(),
            );
        }

        return $post;
    }
}
