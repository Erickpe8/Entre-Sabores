<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Storage::fake('public');

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7YQxQAAAAASUVORK5CYII=');

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'Apellido',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'country' => 'Colombia',
            'profile_photo' => UploadedFile::fake()->createWithContent('profile.png', $png),
            'description' => 'Bio de prueba',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'Apellido',
            'username' => $user->username,
            'country' => 'Colombia',
            'description' => 'Bio de prueba',
        ]);

        $photoPath = (string) $user->profile_photo;
        $this->assertStringContainsString('profiles/'.$user->username.'/', $photoPath);
        Storage::disk('public')->assertExists($photoPath);

        $this->assertNotSame('testapellido', $user->username, 'El username no debe ser el slug de nombre+apellido; debe ser creativo o desde Instagram.');
        $this->assertStringStartsWith('test', $user->username);
    }

    public function test_profile_photo_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'Apellido',
            'email' => 'test2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'country' => 'Mexico',
            'description' => 'Sin foto',
        ]);

        $response->assertSessionHasErrors('profile_photo');
        $this->assertGuest();
    }
}
