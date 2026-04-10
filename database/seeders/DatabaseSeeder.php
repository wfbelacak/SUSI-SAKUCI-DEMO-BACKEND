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
        // Call seeders in correct order (due to foreign key constraints)
        $this->call([
            SiswaSeeder::class,
            AdminSeeder::class,
            KategoriSeeder::class,
            InputAspirasiSeeder::class,
            AspirasiSeeder::class,
        ]);
    }
}
