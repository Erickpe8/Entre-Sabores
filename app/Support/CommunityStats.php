<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CommunityStats
{
    private const CACHE_KEY = 'community:marketing-stats';

    private const CACHE_TTL_SECONDS = 300;

    /**
     * @return array{totalUsers: int, recentMembers: Collection<int, User>}
     */
    public static function forMarketing(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, static function (): array {
                return [
                    'totalUsers' => User::query()->count(),
                    'recentMembers' => User::query()
                        ->latest('created_at')
                        ->limit(3)
                        ->get(),
                ];
            });
        } catch (\Throwable) {
            return [
                'totalUsers' => 0,
                'recentMembers' => collect(),
            ];
        }
    }
}
