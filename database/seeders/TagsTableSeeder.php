<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Breakfast',
            'Lunch',
            'Dinner',
            'Appetizer',
            'Dessert',
            'Vegan',
            'Vegetarian',
            'Gluten-Free',
            'Dairy-Free',
            'Keto',
            'Low-Carb',
            'High-Protein',
            'Quick Meals',
            'Slow Cooker',
            'Italian',
            'Mexican',
            'Asian',
            'Indian',
            'Mediterranean',
            'American'
        ];

        foreach ($tags as $tag) {
            \App\Models\Tag::firstOrCreate(['name' => $tag]);
        }
    }
}
