<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'content' => fake()->paragraphs(3, true),
            'food' => fake()->randomElement(['Tacos', 'Arepa', 'Empanada', 'Ceviche']),
            'drink' => fake()->randomElement(['Café', 'Agua panela', 'Vino', 'Chocolate']),
            'image_path' => null,
            'status' => Post::STATUS_ACTIVE,
            'analysis_status' => Post::ANALYSIS_STATUS_COMPLETED,
            'analysis_result' => null,
            'moderation_reason' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Post $post): void {
            $country = Tag::query()->where('type', Tag::TYPE_COUNTRY)->inRandomOrder()->first();
            $food = Tag::query()->where('type', Tag::TYPE_FOOD_TYPE)->inRandomOrder()->first();
            $experience = Tag::query()->where('type', Tag::TYPE_EXPERIENCE)->inRandomOrder()->first();
            $drink = Tag::query()->where('type', Tag::TYPE_DRINK)->inRandomOrder()->first();

            $ids = array_filter([
                $country?->id,
                $food?->id,
                $experience?->id,
                $drink?->id,
            ]);

            if ($ids !== []) {
                $post->tags()->sync($ids);
            }
        });
    }
}
