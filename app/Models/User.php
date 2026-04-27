<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['first_name', 'last_name', 'username', 'email', 'password', 'country', 'description', 'profile_photo', 'instagram', 'linkedin', 'birthdate', 'preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    public const PREFERENCE_OPTIONS = [
        'Amante del vino',
        'Café lover',
        'Comida rápida',
        'Gastronomía gourmet',
        'Street food',
        'Postres',
        'Comida tradicional',
        'Explorador culinario',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'preferences' => 'array',
        ];
    }

    /**
     * Genera un username único tipo slug a partir del nombre (para rutas /user/{username} y carpetas en storage).
     */
    public static function generateUniqueUsername(string $firstName, string $lastName): string
    {
        $base = Str::slug($firstName.$lastName, '');
        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $original = $username;
        $count = 1;

        while (static::query()->where('username', $username)->exists()) {
            $username = $original.$count;
            $count++;
        }

        return $username;
    }

    /**
     * URL pública de la foto de perfil (disco public) o imagen por defecto.
     * Ruta en BD: p. ej. "profiles/archivo.jpg" relativa a storage/app/public (sin prefijo storage/).
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $normalized = self::normalizePublicDiskPath($this->profile_photo);
            if ($normalized === null) {
                return '/images/default.png';
            }

            return '/storage/'.ltrim($normalized, '/');
        });
    }

    /**
     * Normaliza valores mal guardados (storage/..., public/storage/...) a la ruta del disco public.
     */
    public static function normalizePublicDiskPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path), '/');

        foreach (['public/storage/', 'public/', 'storage/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
                break;
            }
        }

        return $path !== '' ? $path : null;
    }
}
