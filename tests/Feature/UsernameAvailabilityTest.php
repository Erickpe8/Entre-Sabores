<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_username_as_taken_when_it_exists(): void
    {
        User::factory()->create(['username' => 'taken_name']);

        $response = $this->getJson('/username/availability?username=taken_name');

        $response->assertOk();
        $response->assertJson([
            'available' => false,
            'username' => 'taken_name',
        ]);
    }

    public function test_guest_sees_username_as_available_when_free(): void
    {
        $response = $this->getJson('/username/availability?username=libre_unico');

        $response->assertOk();
        $response->assertJson([
            'available' => true,
            'username' => 'libre_unico',
        ]);
    }

    public function test_authenticated_user_ignores_own_username(): void
    {
        $user = User::factory()->create(['username' => 'yo_mismo']);

        $response = $this
            ->actingAs($user)
            ->getJson('/username/availability?username=yo_mismo');

        $response->assertOk();
        $response->assertJson([
            'available' => true,
            'username' => 'yo_mismo',
        ]);
    }

    public function test_authenticated_user_cannot_steal_another_users_username(): void
    {
        $other = User::factory()->create(['username' => 'otro_user']);

        $user = User::factory()->create(['username' => 'mi_user']);

        $response = $this
            ->actingAs($user)
            ->getJson('/username/availability?username=otro_user');

        $response->assertOk();
        $response->assertJson([
            'available' => false,
            'username' => 'otro_user',
        ]);
    }

    public function test_invalid_username_returns_422(): void
    {
        $this->getJson('/username/availability?username=ab')->assertUnprocessable();
    }
}
