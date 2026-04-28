<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterPostsRequest;
use App\Models\Country;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WallController extends Controller
{
    public function index(): View
    {
        $countries = Country::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('wall.index', [
            'countries' => $countries,
            'wallConfig' => [
                'filterUrl' => route('posts.filter'),
                'postBaseUrl' => url('/posts'),
                'loginUrl' => route('login'),
                'isAuthenticated' => auth()->check(),
                'initialFollowing' => request()->boolean('following'),
            ],
        ]);
    }

    public function filter(FilterPostsRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 12);
        $page = $request->integer('page', 1);

        $query = Post::query()
            ->with(['user:id,first_name,last_name,username,profile_photo', 'country:id,name,flag_emoji'])
            ->withCount('comments');

        if ($request->boolean('following')) {
            if (! auth()->check()) {
                return response()->json([
                    'posts' => [],
                    'meta' => ['guest_following' => true, 'has_more' => false],
                ]);
            }

            $ids = DB::table('follows')->where('follower_id', auth()->id())->pluck('following_id');
            if ($ids->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('user_id', $ids);
            }
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        if ($request->filled('experience_type')) {
            $query->where('experience_type', $request->string('experience_type'));
        }

        if ($request->filled('drink_type')) {
            $query->where('drink_type', $request->string('drink_type'));
        }

        $sort = $request->input('sort', 'recent');
        if ($sort === 'popular') {
            $query->orderByDesc('comments_count')->orderByDesc('created_at');
        } else {
            $query->latest();
        }

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'posts' => collect($posts->items())->map(fn (Post $post) => $this->serializePost($post))->values()->all(),
            'meta' => [
                'total_posts' => $posts->total(),
                'limit' => $perPage,
                'current_page' => $posts->currentPage(),
                'next_page' => $posts->currentPage() < $posts->lastPage() ? $posts->currentPage() + 1 : null,
                'has_more' => $posts->hasMorePages(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePost(Post $post): array
    {
        $user = $post->user;

        return [
            'id' => $post->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt(),
            'food_label' => $post->food_label,
            'drink_label' => $post->drink_label,
            'experience_type' => $post->experience_type,
            'drink_type' => $post->drink_type,
            'comments_count' => (int) ($post->comments_count ?? 0),
            'created_at' => $post->created_at?->toIso8601String(),
            'country_id' => $post->country_id,
            'country' => $post->relationLoaded('country') && $post->country !== null
                ? [
                    'name' => $post->country->name,
                    'flag_emoji' => $post->country->flag_emoji,
                ]
                : null,
            'user' => [
                'id' => $user->id,
                'name' => trim($user->first_name.' '.$user->last_name),
                'username' => $user->username,
                'avatar' => $user->profile_photo_url,
            ],
        ];
    }
}
