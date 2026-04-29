<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Colombia', 'slug' => 'colombia', 'iso_code' => 'CO', 'sort_order' => 1],
            ['name' => 'México', 'slug' => 'mexico', 'iso_code' => 'MX', 'sort_order' => 2],
            ['name' => 'Argentina', 'slug' => 'argentina', 'iso_code' => 'AR', 'sort_order' => 3],
            ['name' => 'España', 'slug' => 'espana', 'iso_code' => 'ES', 'sort_order' => 4],
        ];

        foreach ($rows as $row) {
            Country::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row
            );
        }
    }
}
