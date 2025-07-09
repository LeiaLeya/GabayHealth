<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RuralHealthUnit;

class RuralHealthUnitSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'approved', 'rejected'];

        for ($i = 0; $i < 20; $i++) {
            RuralHealthUnit::create([
                'name' => fake()->company . ' RHU',
                'tagline' => fake()->catchPhrase,
                'city' => fake()->city,
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }
}