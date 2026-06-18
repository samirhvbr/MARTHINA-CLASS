<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubjectSeeder::class,
            CategorySeeder::class,
            WordSeeder::class,
            QuestionSeeder::class,
        ]);
    }
}
