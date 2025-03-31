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

        // Create recipes with original images
        $recipes = [
            [
                'name' => 'Homemade Pizza',
                'description' => 'Delicious pizza with fresh toppings',
                'recipe_cover_picture' => 'images/pizza.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Garden Salad',
                'description' => 'Fresh and healthy garden salad',
                'recipe_cover_picture' => 'images/salad.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Chocolate Donuts',
                'description' => 'Sweet and fluffy chocolate donuts',
                'recipe_cover_picture' => 'images/donuts.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Grilled Chicken',
                'description' => 'Perfectly grilled chicken with herbs',
                'recipe_cover_picture' => 'images/chicken.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ],
            [
                'name' => 'Fruit Smoothie',
                'description' => 'Refreshing fruit smoothie blend',
                'recipe_cover_picture' => 'images/smoothie.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ]
        ];

        foreach ($recipes as $recipe) {
            Recipe::create($recipe);
        }
    }
}
