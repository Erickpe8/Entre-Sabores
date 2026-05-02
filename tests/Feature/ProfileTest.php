<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/settings/profile');

        $response->assertOk();
    }

    public function test_authenticated_profile_root_redirects_to_settings(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('settings.profile'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'username' => $user->username,
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'country' => $user->country,
                'profile_edit_form' => '1',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $user->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'username' => $user->username,
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $user->email,
                'country' => $user->country,
                'profile_edit_form' => '1',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/settings/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/settings/profile')
            ->delete('/settings/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_legacy_user_url_redirects_to_profile_show(): void
    {
        $user = User::factory()->create(['username' => 'legacyuser']);

        $this
            ->get('/user/legacyuser')
            ->assertRedirect(route('profile.show', ['username' => 'legacyuser']));
    }

    public function test_public_profile_page_is_displayed_by_username(): void
    {
        $user = User::factory()->create([
            'username' => 'testuserpub',
            'description' => 'Hola mundo',
        ]);

        $response = $this->get(route('profile.show', 'testuserpub'));

        $response->assertOk();
        $response->assertSee('@testuserpub', false);
        $response->assertSee('Hola mundo', false);
    }

    public function test_public_profile_returns_not_found_for_unknown_username(): void
    {
        $this->get(route('profile.show', 'usuario_inexistente_xyz'))->assertNotFound();
    }

    public function test_profile_posts_json_rejects_per_page_above_limit(): void
    {
        $user = User::factory()->create(['username' => 'pageruser']);

        $this->getJson(route('users.posts.index', ['username' => $user->username]).'?per_page=99')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_profile_posts_json_accepts_per_page_within_limit(): void
    {
        $user = User::factory()->create(['username' => 'pageruserok']);

        $this->getJson(route('users.posts.index', ['username' => $user->username]).'?per_page=30')
            ->assertOk();
    }
}
