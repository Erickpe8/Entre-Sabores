<?php

namespace App\View\Components\Ui;

use App\Support\CommunityStats;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class CommunitySocialProof extends Component
{
    public int $totalUsers;

    /** @var Collection<int, object{username: string, profile_photo: ?string, profile_photo_thumb_url: ?string, initials: string}> */
    public Collection $recentMembers;

    public function __construct()
    {
        $stats = CommunityStats::forMarketing();
        $this->totalUsers = $stats['totalUsers'];
        $recentMembers = $stats['recentMembers'];
        $this->recentMembers = $recentMembers instanceof Collection
            ? $recentMembers
            : collect();
    }

    public function render(): View|Closure|string
    {
        return view('components.ui.community-social-proof');
    }
}
