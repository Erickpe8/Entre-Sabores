<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

final class CommunityStats
{
    /**
     * @return array{totalUsers: int, recentMembers: Collection<int, User>}
     */
    public static function forMarketing(): array
    {
        return [
            'totalUsers' => User::query()->count(),
            'recentMembers' => User::query()
                ->latest('created_at')
                ->limit(3)
                ->get(),
        ];
    }
}
