<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Colombia', 'slug' => 'colombia', 'flag_emoji' => '🇨🇴', 'sort_order' => 1],
            ['name' => 'México', 'slug' => 'mexico', 'flag_emoji' => '🇲🇽', 'sort_order' => 2],
            ['name' => 'Argentina', 'slug' => 'argentina', 'flag_emoji' => '🇦🇷', 'sort_order' => 3],
            ['name' => 'España', 'slug' => 'espana', 'flag_emoji' => '🇪🇸', 'sort_order' => 4],
        ];

        foreach ($rows as $row) {
            Country::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row
            );
        }
    }
}
