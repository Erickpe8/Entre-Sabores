<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterPostsRequest;
use App\Models\Tag;
use App\Services\WallFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WallController extends Controller
{
    public function __construct(
        private WallFeedService $wallFeed,
    ) {}

    public function index(): View
    {
        $tags = Tag::cachedCatalog();

        $tagsByType = $tags->groupBy('type')->map(
            fn ($group) => $group->map(fn (Tag $t) => [
                'id' => $t->id,
                'type' => $t->type,
                'slug' => $t->slug,
                'name' => $t->name,
            ])->values()->all()
        )->all();

        return view('wall.index', [
            'tagsByType' => $tagsByType,
            'wallConfig' => [
                // Rutas relativas: si APP_URL no incluye el puerto (p. ej. :8080), las URLs absolutas
                // apuntan a otro origen y Axios no envía la cookie de sesión → 401 en POST.
                'filterUrl' => route('posts.filter', [], false),
                'postStoreUrl' => route('posts.store', [], false),
                'tagsIndexUrl' => route('tags.index', [], false),
                'tagsSearchUrl' => route('tags.search', [], false),
                'postBaseUrl' => '/posts',
                'loginUrl' => route('login', [], false),
                'isAuthenticated' => auth()->check(),
                'initialFollowing' => request()->boolean('following'),
                'tagsByType' => $tagsByType,
            ],
        ]);
    }

    public function filter(FilterPostsRequest $request): JsonResponse
    {
        return $this->wallFeed->respond($request);
    }
}
