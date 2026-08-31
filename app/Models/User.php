<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Illustrations;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['first_name', 'last_name', 'username', 'email', 'password', 'country', 'description', 'profile_photo', 'instagram', 'linkedin', 'birthdate', 'preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Publicaciones a las que este usuario dio «me gusta» (tabla pivote `likes`).
     *
     * @return BelongsToMany<Post, $this>
     */
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'likes')->withTimestamps();
    }

    /**
     * Usuarios que este usuario sigue.
     *
     * @return BelongsToMany<User, $this>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    /**
     * Seguidores de este usuario.
     *
     * @return BelongsToMany<User, $this>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

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
            'unread_notifications_count' => 'integer',
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
     * Ruta en BD: p. ej. "profiles/{usuario}/avatar.webp" relativa al disco public.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $normalized = self::normalizePublicDiskPath($this->profile_photo);
            if ($normalized === null) {
                return Illustrations::defaultAvatarPath();
            }

            return '/storage/'.ltrim($normalized, '/');
        });
    }

    /** Iniciales para avatares fallback (marketing, navbar, etc.). */
    protected function initials(): Attribute
    {
        return Attribute::get(function (): string {
            $initials = strtoupper(
                mb_substr((string) ($this->first_name ?? ''), 0, 1)
                .mb_substr((string) ($this->last_name ?? ''), 0, 1)
            );

            if (trim($initials) === '') {
                return strtoupper(mb_substr((string) ($this->username ?? 'U'), 0, 2));
            }

            return $initials;
        });
    }

    /**
     * Variante pequeña (p. ej. listados); si no existe, coincide con la foto principal.
     */
    protected function profilePhotoThumbUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->publicUrlForProfileVariant((string) config('profile_photo.filenames.thumb', 'avatar_thumb.webp'))
                ?? $this->profile_photo_url;
        });
    }

    /**
     * Variante media (p. ej. cabeceras compactas).
     */
    protected function profilePhotoMediumUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->publicUrlForProfileVariant((string) config('profile_photo.filenames.medium', 'avatar_medium.webp'))
                ?? $this->profile_photo_url;
        });
    }

    /**
     * @return string|null URL bajo /storage/… o null si el archivo no está en disco
     */
    private function publicUrlForProfileVariant(string $filename): ?string
    {
        $base = self::normalizePublicDiskPath($this->profile_photo);
        if ($base === null) {
            return null;
        }

        $dir = dirname($base);
        if ($dir === '.' || $dir === '') {
            return null;
        }

        $relative = $dir.'/'.$filename;
        if (! Storage::disk('public')->exists($relative)) {
            return null;
        }

        return '/storage/'.ltrim($relative, '/');
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
