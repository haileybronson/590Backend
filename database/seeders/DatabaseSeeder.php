<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        // Seed recipes with S3 images
        $recipes = [
            [
                'name' => 'Delicious Donuts',
                'description' => 'Sweet and fluffy homemade donuts perfect for breakfast or dessert',
                'recipe_cover_picture' => 'images/donuts.jpg'
            ],
            [
                'name' => 'Classic Pasta',
                'description' => 'Traditional Italian pasta dish with rich tomato sauce',
                'recipe_cover_picture' => 'images/pasta.png'
            ],
            [
                'name' => 'Pizza Margherita',
                'description' => 'Authentic Neapolitan pizza with fresh basil and mozzarella',
                'recipe_cover_picture' => 'images/pizza.jpg'
            ],
            [
                'name' => 'Fresh Garden Salad',
                'description' => 'Healthy and crisp salad with seasonal vegetables',
                'recipe_cover_picture' => 'images/salad.jpg'
            ],
            [
                'name' => 'Gourmet Sandwich',
                'description' => 'Artisanal sandwich with premium ingredients and fresh bread',
                'recipe_cover_picture' => 'images/sandwich.png'
            ]
        ];

        foreach ($recipes as $recipe) {
            Recipe::create($recipe);
        }
    }
}
