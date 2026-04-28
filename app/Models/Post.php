<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const EXPERIENCE_TYPES = [
        'tradicional',
        'callejero',
        'gourmet',
        'dulce',
        'salado',
    ];

    public const DRINK_TYPES = [
        'cafe',
        'vino',
        'cerveza',
        'tradicional',
    ];

    protected $fillable = [
        'user_id',
        'country_id',
        'title',
        'story',
        'food_label',
        'drink_label',
        'experience_type',
        'drink_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function excerpt(): string
    {
        return Str::limit(trim(strip_tags($this->story)), 80);
    }
}
