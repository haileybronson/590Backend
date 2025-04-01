<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create recipes with the exact image names from S3
        $recipes = [
            [
                'name' => 'Chocolate Donuts',
                'description' => 'Sweet and fluffy chocolate donuts perfect for breakfast or dessert',
                'recipe_cover_picture' => 'images/donuts.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Classic Pasta',
                'description' => 'Traditional Italian pasta dish with rich tomato sauce',
                'recipe_cover_picture' => 'images/pasta.png',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Pizza Margherita',
                'description' => 'Authentic Neapolitan pizza with fresh basil and mozzarella',
                'recipe_cover_picture' => 'images/pizza.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Fresh Garden Salad',
                'description' => 'Healthy and crisp salad with seasonal vegetables',
                'recipe_cover_picture' => 'images/salad.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Gourmet Sandwich',
                'description' => 'Artisanal sandwich with premium ingredients and fresh bread',
                'recipe_cover_picture' => 'images/sandwich.png',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ]
        ];

        foreach ($recipes as $recipe) {
            Recipe::create($recipe);
        }
    }
}
