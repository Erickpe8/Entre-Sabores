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
     * Normaliza un handle de Instagram para derivar username (sin @).
     */
    public static function normalizeInstagramHandle(?string $handle): ?string
    {
        if ($handle === null || $handle === '') {
            return null;
        }

        $handle = trim($handle);
        $handle = ltrim($handle, '@/');
        $handle = trim($handle);

        return $handle !== '' ? $handle : null;
    }

    /** @var list<string> */
    private const CREATIVE_FOODS = [
        'arepa', 'taco', 'empanada', 'ajiaco', 'tamal', 'ceviche', 'pozole', 'sancocho',
        'mole', 'paella', 'gazpacho', 'churro', 'torta', 'burrito', 'quesadilla', 'pupusa',
        'enchilada', 'hallaca', 'asado', 'patacon', 'cazuela', 'horchata',
    ];

    /**
     * Base creativa para username: primer nombre + comida en español + número.
     */
    public static function creativeUsernameBase(string $firstName): string
    {
        $firstToken = trim((string) Str::of($firstName)->explode(' ')->first());
        $namePart = Str::slug($firstToken, '');
        $food = self::CREATIVE_FOODS[array_rand(self::CREATIVE_FOODS)];
        $salt = (string) random_int(10, 99);

        $raw = $namePart.$food.$salt;
        $base = Str::slug($raw, '');

        if ($base === '') {
            $base = 'usuario'.self::CREATIVE_FOODS[array_rand(self::CREATIVE_FOODS)].(string) random_int(10, 99);
        }

        if (strlen($base) > 24) {
            $base = substr($base, 0, 24);
        }

        return $base;
    }

    /**
     * Genera un username único (minúsculas, slug): prioriza Instagram si viene informado;
     * si no, usa primer nombre + comida + número para un alias divertido.
     */
    public static function generateUniqueUsername(string $firstName, string $lastName, ?string $instagramHandle = null): string
    {
        // $lastName se mantiene por compatibilidad (registro/factory) aunque no se use para el fallback creativo.
        unset($lastName);
        $instagramHandle = self::normalizeInstagramHandle($instagramHandle);

        if ($instagramHandle !== null) {
            $base = Str::slug($instagramHandle, '');
        } else {
            $base = self::creativeUsernameBase($firstName);
        }

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
