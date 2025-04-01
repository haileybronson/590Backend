<?php

namespace App\Http\Controllers\API;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecipeController extends BaseController
{
    public function getS3Url($path, $minutes = 10)
    {
        if (!$path) return null;
        return "https://" . env('AWS_BUCKET') . ".s3." . env('AWS_DEFAULT_REGION') . ".amazonaws.com/" . $path;
    }

    public function index()
    {
        $recipes = Recipe::all();

        // Add S3 URLs for recipe cover pictures
        foreach ($recipes as $recipe) {
            if ($recipe->recipe_cover_picture) {
                $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);
            }
        }

        return $this->sendResponse($recipes, 'Recipes retrieved successfully.');
    }

    public function store(Request $request)
    {
        try {
            \Log::info('Received recipe creation request', [
                'has_file' => $request->hasFile('recipe_cover_picture'),
                'all_data' => $request->all()
            ]);

            $validator = \Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
                'recipe_cover_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed', ['errors' => $validator->errors()]);
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            if (!$request->hasFile('recipe_cover_picture')) {
                \Log::error('No image file in request');
                return $this->sendError('No image file found in request', [], 400);
            }

            $file = $request->file('recipe_cover_picture');
            \Log::info('Image file details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);

            $filename = time() . '_' . $file->getClientOriginalName();

            // Store the file in S3
            $path = $file->storeAs('images', $filename, 's3');

            if (!$path) {
                throw new \Exception('Failed to upload image to S3');
            }

            // Set the file to be publicly accessible
            Storage::disk('s3')->setVisibility($path, 'public');

            // Create recipe with S3 path
            $recipe = Recipe::create([
                'name' => $request->name,
                'description' => $request->description,
                'recipe_cover_picture' => $path,
                'inventory_total_qty' => 1,
                'checked_qty' => 0
            ]);

            // Add S3 URL for response
            $recipe->recipe_cover_picture = $this->getS3Url($path);

            return $this->sendResponse($recipe, 'Recipe created successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to create recipe: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Failed to create recipe: ' . $e->getMessage(), [], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'recipe_cover_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        try {
            $recipe = Recipe::findOrFail($id);

            // Update basic info
            $recipe->name = $request->name;
            $recipe->description = $request->description;

            // Handle image update if provided
            if ($request->hasFile('recipe_cover_picture')) {
                // Delete old image from S3 if it exists
                if ($recipe->recipe_cover_picture) {
                    Storage::disk('s3')->delete($recipe->recipe_cover_picture);
                }

                $file = $request->file('recipe_cover_picture');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Store the new file in S3
                $path = $file->storeAs('images', $filename, 's3');

                if (!$path) {
                    throw new \Exception('Failed to upload image to S3');
                }

                // Set the file to be publicly accessible
                Storage::disk('s3')->setVisibility($path, 'public');
                $recipe->recipe_cover_picture = $path;
            }

            $recipe->save();

            // Add S3 URL for response
            if ($recipe->recipe_cover_picture) {
                $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);
            }

            return $this->sendResponse($recipe, 'Recipe updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to update recipe: ' . $e->getMessage());
            return $this->sendError('Failed to update recipe: ' . $e->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            // Delete image from S3 if it exists
            if ($recipe->recipe_cover_picture) {
                Storage::disk('s3')->delete($recipe->recipe_cover_picture);
            }

            $recipe->delete();

            return $this->sendResponse([], 'Recipe deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete recipe: ' . $e->getMessage(), [], 500);
        }
    }

    public function checkout($id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            if ($recipe->checked_qty >= $recipe->inventory_total_qty) {
                return $this->sendError('Recipe is out of stock', [], 400);
            }

            $recipe->checked_qty += 1;
            $recipe->save();

            // Add S3 URL for response
            $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);

            return $this->sendResponse($recipe, 'Recipe checked out successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to checkout recipe: ' . $e->getMessage(), [], 500);
        }
    }

    public function return($id)
    {
        try {
            $recipe = Recipe::findOrFail($id);

            if ($recipe->checked_qty <= 0) {
                return $this->sendError('Recipe is not checked out', [], 400);
            }

            $recipe->checked_qty -= 1;
            $recipe->save();

            // Add S3 URL for response
            $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);

            return $this->sendResponse($recipe, 'Recipe returned successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to return recipe: ' . $e->getMessage(), [], 500);
        }
    }
}
