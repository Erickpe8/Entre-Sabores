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

    /** @var Collection<int, \App\Models\User> */
    public Collection $recentMembers;

    public function __construct()
    {
        $stats = CommunityStats::forMarketing();
        $this->totalUsers = $stats['totalUsers'];
        $this->recentMembers = $stats['recentMembers'];
    }

    public function render(): View|Closure|string
    {
        return view('components.ui.community-social-proof');
    }
}
