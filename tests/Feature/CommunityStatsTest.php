<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\CommunityStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CommunityStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_marketing_caches_serializable_member_payloads(): void
    {
        User::factory()->create(['username' => 'chef_uno']);

        Cache::flush();

        $first = CommunityStats::forMarketing();
        $this->assertSame(1, $first['totalUsers']);
        $this->assertCount(1, $first['recentMembers']);
        $this->assertSame('chef_uno', $first['recentMembers']->first()->username);

        $raw = Cache::get('community:marketing-stats:v2');
        $this->assertIsArray($raw['recentMembers'] ?? null);
        $this->assertIsArray($raw['recentMembers'][0] ?? null);
        $this->assertArrayHasKey('username', $raw['recentMembers'][0]);

        $second = CommunityStats::forMarketing();
        $this->assertSame('chef_uno', $second['recentMembers']->first()->username);
    }

    public function test_welcome_page_renders_with_community_social_proof(): void
    {
        User::factory()->count(2)->create();

        Cache::flush();

        $this->get(route('welcome'))->assertOk()->assertSee('gastrónomos ya se unieron', false);
    }
}
