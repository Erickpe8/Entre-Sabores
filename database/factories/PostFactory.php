<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Post;
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
        $foods = ['Arepa con hogao', 'Tacos al pastor', 'Bandeja paisa', 'Mole poblano', 'Ceviche', 'Empanadas'];
        $drinks = ['Café de origen', 'Vino tinto reserva', 'Cerveza artesanal', 'Aguardiente', 'Tequila reposado', 'Chocolate caliente'];

        return [
            'user_id' => User::factory(),
            'country_id' => Country::factory(),
            'title' => fake()->sentence(4),
            'story' => fake()->paragraphs(3, true),
            'food_label' => fake()->randomElement($foods),
            'drink_label' => fake()->randomElement($drinks),
            'experience_type' => fake()->randomElement(Post::EXPERIENCE_TYPES),
            'drink_type' => fake()->randomElement(Post::DRINK_TYPES),
        ];
    }
}
