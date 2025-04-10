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
            TagsTableSeeder::class,
            IngredientsTableSeeder::class,
        ]);

        // Create test user
        $user = User::firstOrCreate(
            ['email' => 'test@test.com'],
            [
                'name' => 'Test User',
                'email' => 'test@test.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create recipes with the exact image names from S3
        $recipes = [
            [
                'name' => 'Chocolate Donuts',
                'description' => 'Sweet and fluffy chocolate donuts perfect for breakfast or dessert',
                'recipe_cover_picture' => 'images/donuts.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0,
                'user_id' => $user->id,
                'tags' => ['Breakfast', 'Dessert'],
                'ingredients' => [
                    ['name' => 'Flour', 'quantity' => 2],
                    ['name' => 'Sugar', 'quantity' => 1],
                    ['name' => 'Eggs', 'quantity' => 2],
                    ['name' => 'Milk', 'quantity' => 0.75],
                ],
                'nutrition' => [
                    'calories' => 350,
                    'protein' => 4,
                    'carbs' => 45,
                    'fat' => 18,
                    'sugar' => 22
                ]
            ],
            [
                'name' => 'Classic Pasta',
                'description' => 'Traditional Italian pasta dish with rich tomato sauce',
                'recipe_cover_picture' => 'images/pasta.png',
                'inventory_total_qty' => 1,
                'checked_qty' => 0,
                'user_id' => $user->id,
                'tags' => ['Dinner', 'Italian'],
                'ingredients' => [
                    ['name' => 'Pasta', 'quantity' => 8],
                    ['name' => 'Tomatoes', 'quantity' => 3],
                    ['name' => 'Garlic', 'quantity' => 2],
                    ['name' => 'Olive Oil', 'quantity' => 2],
                ],
                'nutrition' => [
                    'calories' => 450,
                    'protein' => 12,
                    'carbs' => 65,
                    'fat' => 15,
                    'fiber' => 4
                ]
            ],
            [
                'name' => 'Pizza Margherita',
                'description' => 'Authentic Neapolitan pizza with fresh basil and mozzarella',
                'recipe_cover_picture' => 'images/pizza.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0,
                'user_id' => $user->id,
                'tags' => ['Dinner', 'Italian', 'Vegetarian'],
                'ingredients' => [
                    ['name' => 'Flour', 'quantity' => 2.5],
                    ['name' => 'Tomatoes', 'quantity' => 2],
                    ['name' => 'Olive Oil', 'quantity' => 1],
                ],
                'nutrition' => [
                    'calories' => 550,
                    'protein' => 20,
                    'carbs' => 70,
                    'fat' => 22
                ]
            ],
            [
                'name' => 'Fresh Garden Salad',
                'description' => 'Healthy and crisp salad with seasonal vegetables',
                'recipe_cover_picture' => 'images/salad.jpg',
                'inventory_total_qty' => 1,
                'checked_qty' => 0,
                'user_id' => $user->id,
                'tags' => ['Lunch', 'Vegetarian', 'Vegan', 'Gluten-Free'],
                'ingredients' => [
                    ['name' => 'Tomatoes', 'quantity' => 2],
                    ['name' => 'Bell Peppers', 'quantity' => 1],
                    ['name' => 'Carrots', 'quantity' => 2],
                    ['name' => 'Olive Oil', 'quantity' => 1],
                ],
                'nutrition' => [
                    'calories' => 150,
                    'protein' => 3,
                    'carbs' => 15,
                    'fat' => 9,
                    'fiber' => 5
                ]
            ],
            [
                'name' => 'Gourmet Sandwich',
                'description' => 'Artisanal sandwich with premium ingredients and fresh bread',
                'recipe_cover_picture' => 'images/sandwich.png',
                'inventory_total_qty' => 1,
                'checked_qty' => 0,
                'user_id' => $user->id,
                'tags' => ['Lunch', 'Quick Meals'],
                'ingredients' => [
                    ['name' => 'Flour', 'quantity' => 1, 'notes' => 'For bread'],
                    ['name' => 'Eggs', 'quantity' => 1],
                    ['name' => 'Tomatoes', 'quantity' => 1],
                    ['name' => 'Pepper', 'quantity' => 0.5],
                ],
                'nutrition' => [
                    'calories' => 400,
                    'protein' => 15,
                    'carbs' => 45,
                    'fat' => 20
                ]
            ]
        ];

        foreach ($recipes as $recipeData) {
            $tagNames = $recipeData['tags'] ?? [];
            $ingredientData = $recipeData['ingredients'] ?? [];
            $nutritionData = $recipeData['nutrition'] ?? null;

            // Remove non-recipe fields
            unset($recipeData['tags']);
            unset($recipeData['ingredients']);
            unset($recipeData['nutrition']);

            // Check if recipe already exists
            $recipe = Recipe::firstWhere('name', $recipeData['name']);

            // If it doesn't exist, create it
            if (!$recipe) {
                $recipe = Recipe::create($recipeData);
            } else {
                // Update user_id if it's not set
                if (!$recipe->user_id && isset($recipeData['user_id'])) {
                    $recipe->user_id = $recipeData['user_id'];
                    $recipe->save();
                }
            }

            // Attach tags
            if (!empty($tagNames)) {
                $tagIds = \App\Models\Tag::whereIn('name', $tagNames)->pluck('id');
                // Sync without detaching to avoid removing existing tags
                $recipe->tags()->syncWithoutDetaching($tagIds);
            }

            // Attach ingredients
            if (!empty($ingredientData)) {
                foreach ($ingredientData as $item) {
                    $ingredient = \App\Models\Ingredient::where('name', $item['name'])->first();
                    if ($ingredient) {
                        // Check if the ingredient is already attached
                        if (!$recipe->ingredients()->where('ingredient_id', $ingredient->id)->exists()) {
                            $recipe->ingredients()->attach($ingredient->id, [
                                'quantity' => $item['quantity'] ?? null,
                                'notes' => $item['notes'] ?? null
                            ]);
                        }
                    }
                }
            }

            // Create nutrition info if it doesn't exist
            if (!empty($nutritionData) && !$recipe->nutritionInfo) {
                $nutritionData['recipe_id'] = $recipe->id;
                \App\Models\NutritionInfo::create($nutritionData);
            }
        }
    }
}
