<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPostsRequest;
use App\Http\Resources\PostResource;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Perfil público por username (sin autenticación obligatoria).
     */
    public function show(string $username): View
    {
        $user = User::query()
            ->where('username', $username)
            ->withCount(['followers', 'following', 'posts'])
            ->firstOrFail();

        $viewerFollows = false;
        if (auth()->check() && auth()->id() !== $user->id) {
            $viewerFollows = auth()->user()->following()->where('following_id', $user->id)->exists();
        }

        $likesReceived = Like::query()
            ->whereHas('post', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        return view('profile.show', [
            'user' => $user,
            'followersCount' => (int) $user->followers_count,
            'followingCount' => (int) $user->following_count,
            'postsCount' => (int) $user->posts_count,
            'likesReceived' => (int) $likesReceived,
            'viewerFollows' => $viewerFollows,
            'profilePostsConfig' => [
                'postsUrl' => route('users.posts.index', ['username' => $user->username], false),
                'postPublicBase' => '/posts',
                'loginUrl' => route('login', [], false),
                'isAuthenticated' => auth()->check(),
            ],
        ]);
    }

    /**
     * Publicaciones del usuario (JSON paginado, mismo formato que PostResource).
     */
    public function posts(UserPostsRequest $request, string $username): JsonResponse
    {
        $user = User::query()->where('username', $username)->firstOrFail();

        $perPage = $request->integer('per_page', 12);
        $page = $request->integer('page', 1);

        $query = Post::query()
            ->where('user_id', $user->id)
            ->with([
                'user:id,first_name,last_name,username,profile_photo',
                'tags' => fn ($q) => $q->orderBy('type')->orderBy('sort_order'),
            ])
            ->withCount(['comments', 'likes']);

        if (auth()->check()) {
            $query->withExists([
                'likes as liked_by_me' => fn ($q) => $q->where('user_id', auth()->id()),
            ]);
        }

        $posts = $query->latest()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'posts' => collect($posts->items())
                ->map(fn (Post $post) => (new PostResource($post))->resolve())
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'has_more' => $posts->hasMorePages(),
                'total' => $posts->total(),
            ],
        ]);
    }
}
