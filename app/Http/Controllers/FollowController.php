<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, string $username): JsonResponse
    {
        $target = User::query()->where('username', $username)->firstOrFail();
        $viewer = $request->user();

        if ($viewer->id === $target->id) {
            return response()->json(['message' => 'No puedes seguirte a ti mismo.'], 422);
        }

        if (! $viewer->following()->where('following_id', $target->id)->exists()) {
            $viewer->following()->attach($target->id);
            $target->notify(new NewFollowerNotification($viewer));
        }

        return response()->json([
            'following' => true,
            'followers_count' => $target->followers()->count(),
        ]);
    }

    public function destroy(Request $request, string $username): JsonResponse
    {
        $target = User::query()->where('username', $username)->firstOrFail();
        $viewer = $request->user();

        $viewer->following()->detach($target->id);

        return response()->json([
            'following' => false,
            'followers_count' => $target->followers()->count(),
        ]);
    }
}
