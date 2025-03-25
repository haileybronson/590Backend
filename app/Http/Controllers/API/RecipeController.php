<?php

namespace App\Http\Controllers\API;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecipeController extends BaseController
{
    public function index()
    {
        $recipes = Recipe::all();

        // Add S3 URLs for recipe cover pictures
        foreach ($recipes as $recipe) {
            if ($recipe->recipe_cover_picture) {
                // Use the correct S3 URL format with region
                $recipe->recipe_cover_picture = "https://" . env('AWS_BUCKET') . ".s3." . env('AWS_DEFAULT_REGION') . ".amazonaws.com/" . $recipe->recipe_cover_picture;
            }
        }

        return $this->sendResponse($recipes, 'Recipes retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        if (!$request->hasFile('image')) {
            return $this->sendError('No image file found in request', [], 400);
        }

        // Keep original filename but store in images directory
        $filename = $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs(
            'images',
            $filename,
            's3'
        );
        Storage::disk('s3')->setVisibility($path, 'public');

        // Create recipe with S3 path
        $recipe = Recipe::create([
            'name' => $request->name,
            'description' => $request->description,
            'recipe_cover_picture' => $path
        ]);

        // Add S3 URL for response using correct format
        $recipe->recipe_cover_picture = "https://" . env('AWS_BUCKET') . ".s3." . env('AWS_DEFAULT_REGION') . ".amazonaws.com/" . $path;

        return $this->sendResponse($recipe, 'Recipe created successfully.');
    }
}
