<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CommunityStats
{
    private const CACHE_KEY = 'community:marketing-stats:v2';

    private const CACHE_TTL_SECONDS = 300;

    /**
     * @return array{totalUsers: int, recentMembers: Collection<int, object>}
     */
    public static function forMarketing(): array
    {
        try {
            $cached = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, static function (): array {
                return [
                    'totalUsers' => User::query()->count(),
                    'recentMembers' => User::query()
                        ->latest('created_at')
                        ->limit(3)
                        ->get()
                        ->map(static fn (User $user): array => self::memberPayload($user))
                        ->values()
                        ->all(),
                ];
            });

            return [
                'totalUsers' => (int) ($cached['totalUsers'] ?? 0),
                'recentMembers' => self::membersCollection($cached['recentMembers'] ?? []),
            ];
        } catch (\Throwable) {
            return [
                'totalUsers' => 0,
                'recentMembers' => collect(),
            ];
        }
    }

    /**
     * @return array{username: string, profile_photo: ?string, profile_photo_thumb_url: ?string, initials: string}
     */
    private static function memberPayload(User $user): array
    {
        return [
            'username' => (string) $user->username,
            'profile_photo' => filled($user->profile_photo) ? (string) $user->profile_photo : null,
            'profile_photo_thumb_url' => $user->profile_photo_thumb_url,
            'initials' => (string) $user->initials,
        ];
    }

    /**
     * @param  mixed  $members
     * @return Collection<int, object>
     */
    private static function membersCollection(mixed $members): Collection
    {
        if (! is_array($members)) {
            return collect();
        }

        return collect($members)
            ->filter(static fn (mixed $row): bool => is_array($row) && isset($row['username']))
            ->map(static fn (array $row): object => (object) [
                'username' => (string) $row['username'],
                'profile_photo' => filled($row['profile_photo'] ?? null) ? (string) $row['profile_photo'] : null,
                'profile_photo_thumb_url' => $row['profile_photo_thumb_url'] ?? null,
                'initials' => (string) ($row['initials'] ?? '?'),
            ])
            ->values();
    }
}
