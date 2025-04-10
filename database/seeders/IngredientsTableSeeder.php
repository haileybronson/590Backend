<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            ['name' => 'Flour', 'unit' => 'cups'],
            ['name' => 'Sugar', 'unit' => 'cups'],
            ['name' => 'Salt', 'unit' => 'tsp'],
            ['name' => 'Pepper', 'unit' => 'tsp'],
            ['name' => 'Olive Oil', 'unit' => 'tbsp'],
            ['name' => 'Butter', 'unit' => 'tbsp'],
            ['name' => 'Eggs', 'unit' => ''],
            ['name' => 'Milk', 'unit' => 'cups'],
            ['name' => 'Chicken Breast', 'unit' => 'lbs'],
            ['name' => 'Ground Beef', 'unit' => 'lbs'],
            ['name' => 'Rice', 'unit' => 'cups'],
            ['name' => 'Pasta', 'unit' => 'oz'],
            ['name' => 'Tomatoes', 'unit' => ''],
            ['name' => 'Onions', 'unit' => ''],
            ['name' => 'Garlic', 'unit' => 'cloves'],
            ['name' => 'Carrots', 'unit' => ''],
            ['name' => 'Celery', 'unit' => 'stalks'],
            ['name' => 'Bell Peppers', 'unit' => ''],
            ['name' => 'Lemon', 'unit' => ''],
            ['name' => 'Lime', 'unit' => '']
        ];

        foreach ($ingredients as $ingredient) {
            \App\Models\Ingredient::firstOrCreate(['name' => $ingredient['name']], $ingredient);
        }
    }
}
