<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Country;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('PostSeeder deshabilitado fuera de entorno local.');

            return;
        }

        $user = User::query()->first();
        if ($user === null) {
            return;
        }

        $colombia = Country::query()->where('slug', 'colombia')->first();
        $mexico = Country::query()->where('slug', 'mexico')->first();
        $argentina = Country::query()->where('slug', 'argentina')->first();

        if ($colombia === null || $mexico === null) {
            return;
        }

        $posts = [
            [
                'country_id' => $colombia->id,
                'title' => 'Café de origen con arepa de choclo',
                'story' => "Un domingo en Bogotá descubrimos un pequeño tostadero que marida arepas de choclo calientes con un café de origen de Huila, frutal y con notas a panela. La grasa suave del queso untado equilibra la acidez del café y deja un final largo y reconfortable.\n\nLo mejor: pedir el café en método V60 para resaltar los aromas florales junto al maíz dulce de la arepa.",
                'food_label' => 'Arepa de choclo con queso',
                'drink_label' => 'Café V60 Huila',
                'experience_type' => 'tradicional',
                'drink_type' => 'cafe',
            ],
            [
                'country_id' => $colombia->id,
                'title' => 'Aguardiente y empanadas santandereanas',
                'story' => "El contraste entre el anís del aguardiente antioqueño y la sal de las empanadas de pipián crea un puente sorprendente: primero frescura anisada, luego umami de la carne cocida lentamente.\n\nSirve el aguardiente muy frío y las empanadas recién horneadas.",
                'food_label' => 'Empanadas santandereanas',
                'drink_label' => 'Aguardiente verde',
                'experience_type' => 'callejero',
                'drink_type' => 'tradicional',
            ],
            [
                'country_id' => $mexico->id,
                'title' => 'Tacos al pastor y michelada',
                'story' => "La piña asada de los tacos al pastor pide algo cítrico y refrescante. Una michelada con sal en el borde realza el chile guajillo sin tapar el cerdo marinado.\n\nIdeal para la noche en un puesto de la ciudad: comparte tabla y prueba dos salsas distintas entre taco y taco.",
                'food_label' => 'Tacos al pastor',
                'drink_label' => 'Michelada clásica',
                'experience_type' => 'callejero',
                'drink_type' => 'cerveza',
            ],
            [
                'country_id' => $mexico->id,
                'title' => 'Mole negro Oaxaqueño y mezcal joven',
                'story' => "El mole lleva decenas de ingredientes; el mezcal joven aporta notas herbáneas y ahumadas que dialogan con el chocolate amargo del mole sin dominarlo.\n\nSirve porciones pequeñas: la intensidad es alta y el paladar se fatiga rápido.",
                'food_label' => 'Mole negro con pollo',
                'drink_label' => 'Mezcal espadín',
                'experience_type' => 'gourmet',
                'drink_type' => 'tradicional',
            ],
            [
                'country_id' => $argentina?->id ?? $mexico->id,
                'title' => 'Malbec y empanadas salteñas',
                'story' => "Los taninos del malbec cortan la grasa jugosa de la empanada salteña y amplifican las especias del relleno. Temperatura ambiente para el vino y empanadas calientes.\n\nUn maridaje de domingo que nunca falla entre amigos.",
                'food_label' => 'Empanadas salteñas',
                'drink_label' => 'Malbec reserva',
                'experience_type' => 'tradicional',
                'drink_type' => 'vino',
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::query()->create([
                'user_id' => $user->id,
                'country_id' => $data['country_id'],
                'title' => $data['title'],
                'story' => $data['story'],
                'food_label' => $data['food_label'],
                'drink_label' => $data['drink_label'],
                'experience_type' => $data['experience_type'],
                'drink_type' => $data['drink_type'],
            ]);

            Comment::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'body' => '¡Gran combinación! Lo probaré el fin de semana.',
            ]);
        }
    }
}
