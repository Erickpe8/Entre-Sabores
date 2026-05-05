<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UserPostsRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\ProfilePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Publicaciones que el usuario autenticado marcó con «me gusta» (orden por fecha del like).
     */
    public function likedPosts(UserPostsRequest $request): JsonResponse
    {
        $user = $request->user();

        $perPage = $request->integer('per_page', 10);
        $page = $request->integer('page', 1);

        $query = Post::query()
            ->join('likes', function ($join) use ($user) {
                $join->on('posts.id', '=', 'likes.post_id')
                    ->where('likes.user_id', '=', $user->id);
            })
            ->with([
                'user:id,first_name,last_name,username,profile_photo',
                'tags' => fn ($q) => $q->orderBy('type')->orderBy('sort_order'),
            ])
            ->withCount(['comments', 'likes'])
            ->orderByDesc('likes.created_at')
            ->orderByDesc('likes.id')
            ->select('posts.*');

        $posts = $query->paginate($perPage, ['posts.*'], 'page', $page);

        return response()->json([
            'posts' => collect($posts->items())
                ->map(function (Post $post) {
                    $post->setAttribute('liked_by_me', true);

                    return (new PostResource($post))->resolve();
                })
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

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('settings.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cuenta y seguridad (contraseña, borrar cuenta).
     */
    public function account(Request $request): View
    {
        return view('settings.account', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, ProfilePhotoService $profilePhotos): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $previousUsername = $user->username;
        $targetUsername = $validated['username'] ?? $previousUsername;

        if ($targetUsername !== $previousUsername) {
            $disk = Storage::disk('public');
            $oldDir = 'profiles/'.$previousUsername;
            $newDir = 'profiles/'.$targetUsername;

            if ($disk->exists($oldDir)) {
                $from = $disk->path($oldDir);
                $to = $disk->path($newDir);

                if (! File::isDirectory($to)) {
                    File::moveDirectory($from, $to);
                }

                $photo = $user->profile_photo;
                if (is_string($photo) && $photo !== '' && str_contains($photo, $oldDir.'/')) {
                    $validated['profile_photo'] = str_replace($oldDir.'/', $newDir.'/', $photo);
                }
            }
        }

        $base64 = $validated['profile_photo_base64'] ?? null;
        unset($validated['profile_photo_base64']);

        if (is_string($base64) && $base64 !== '') {
            $image = $base64;
            $image = (string) preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageDecoded = base64_decode($image, true);

            if ($imageDecoded === false) {
                throw ValidationException::withMessages([
                    'profile_photo_base64' => 'No se pudo procesar la imagen del avatar. Intenta con otra foto.',
                ]);
            }

            if (strlen($imageDecoded) > 2048 * 1024) {
                throw ValidationException::withMessages([
                    'profile_photo_base64' => 'La foto de perfil no puede superar los 2 MB.',
                ]);
            }

            $directory = 'profiles/'.$targetUsername;

            $previousPath = $validated['profile_photo'] ?? $user->profile_photo;
            $profilePhotos->deleteStoredPhoto($previousPath);

            $validated['profile_photo'] = $profilePhotos->storeFromBinary($imageDecoded, $directory);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('settings.profile')
            ->with('status', 'profile-updated')
            ->with('success', 'Perfil actualizado correctamente');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
