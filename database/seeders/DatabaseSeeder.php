<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CountrySeeder::class);
        $this->call(TagSeeder::class);

        if (! app()->environment('local')) {
            $this->command?->info('Seeders de desarrollo omitidos (no local).');

            return;
        }

        if (file_exists(database_path('seeders/DevUserSeeder.php'))) {
            $this->call(DevUserSeeder::class);
        }

        $this->call(PostSeeder::class);
    }
}
