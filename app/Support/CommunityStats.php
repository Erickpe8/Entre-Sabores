<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CommunityStats
{
    private const CACHE_KEY_COUNT = 'community:marketing-stats:total-users:v2';

    private const CACHE_TTL_SECONDS = 300;

    /**
     * @return array{totalUsers: int, recentMembers: Collection<int, User>}
     */
    public static function forMarketing(): array
    {
        try {
            $totalUsers = Cache::remember(
                self::CACHE_KEY_COUNT,
                self::CACHE_TTL_SECONDS,
                static fn (): int => User::query()->count(),
            );

            // No cachear modelos Eloquent: al deserializar pueden quedar como __PHP_Incomplete_Class.
            $recentMembers = User::query()
                ->latest('created_at')
                ->limit(3)
                ->get();

            return [
                'totalUsers' => (int) $totalUsers,
                'recentMembers' => $recentMembers,
            ];
        } catch (\Throwable) {
            return [
                'totalUsers' => 0,
                'recentMembers' => collect(),
            ];
        }
    }
}
