<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min($request->integer('limit', 25), 50);

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'read' => $n->read_at !== null,
                'type' => class_basename($n->type),
                'data' => $n->data,
                'created_at' => $n->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        if (! Str::isUuid($id)) {
            abort(404);
        }

        $updated = $request->user()->notifications()->where('id', $id)->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'ok' => $updated > 0,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'ok' => true,
            'unread_count' => 0,
        ]);
    }
}
