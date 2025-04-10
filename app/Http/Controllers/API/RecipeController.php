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
        $recipes = Recipe::with(['tags', 'ingredients', 'nutritionInfo', 'user'])->get();

        // Add S3 URLs for recipe cover pictures
        foreach ($recipes as $recipe) {
            if ($recipe->recipe_cover_picture) {
                $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);
            }
        }

        return $this->sendResponse($recipes, 'Recipes retrieved successfully.');
    }

    public function show($id)
    {
        try {
            $recipe = Recipe::with(['tags', 'ingredients', 'nutritionInfo', 'user'])->findOrFail($id);

            // Add S3 URL for recipe cover picture
            if ($recipe->recipe_cover_picture) {
                $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);
            }

            return $this->sendResponse($recipe, 'Recipe retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Recipe not found.', [], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            \Log::info('Received recipe creation request', [
                'has_file' => $request->hasFile('recipe_cover_picture'),
                'all_data' => $request->all(),
                'files' => $request->allFiles(),
                'content_type' => $request->header('Content-Type'),
                'headers' => $request->headers->all()
            ]);

            $validator = \Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
                'recipe_cover_picture' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed', [
                    'errors' => $validator->errors(),
                    'request_data' => $request->all(),
                    'files' => $request->allFiles(),
                    'file_validation' => [
                        'has_file' => $request->hasFile('recipe_cover_picture'),
                        'is_valid' => $request->file('recipe_cover_picture') ? $request->file('recipe_cover_picture')->isValid() : false,
                        'original_name' => $request->file('recipe_cover_picture') ? $request->file('recipe_cover_picture')->getClientOriginalName() : null,
                        'mime_type' => $request->file('recipe_cover_picture') ? $request->file('recipe_cover_picture')->getMimeType() : null,
                        'extension' => $request->file('recipe_cover_picture') ? $request->file('recipe_cover_picture')->getClientOriginalExtension() : null
                    ]
                ]);
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            if (!$request->hasFile('recipe_cover_picture')) {
                \Log::error('No image file in request', [
                    'files' => $request->allFiles(),
                    'has_file' => $request->hasFile('recipe_cover_picture'),
                    'content_type' => $request->header('Content-Type'),
                    'post_data' => $request->post(),
                    'request_data' => $request->all()
                ]);
                return $this->sendError('No image file found in request', [], 400);
            }

            $file = $request->file('recipe_cover_picture');
            \Log::info('Image file details', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'extension' => $file->getClientOriginalExtension(),
                'path' => $file->getPath(),
                'real_path' => $file->getRealPath(),
                'is_valid' => $file->isValid(),
                'validation_error' => $file->getError()
            ]);

            // Validate file extension manually as backup
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

            if (!in_array($extension, $allowedExtensions)) {
                \Log::error('Invalid file extension', [
                    'extension' => $extension,
                    'allowed' => $allowedExtensions
                ]);
                return $this->sendError('Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions), [], 422);
            }

            $filename = time() . '_' . $file->getClientOriginalName();

            // Store the file in S3
            $path = $file->storeAs('images', $filename, 's3');
            \Log::info('File stored in S3', [
                'path' => $path,
                'filename' => $filename
            ]);

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
            'recipe_cover_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240'
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

            // Check if recipe is checked out
            if ($recipe->checked_qty > 0) {
                return $this->sendError('Cannot delete recipe while it is in use', [], 400);
            }

            // Delete image from S3 if it exists
            if ($recipe->recipe_cover_picture) {
                \Log::info('Deleting image from S3', [
                    'path' => $recipe->recipe_cover_picture
                ]);
                Storage::disk('s3')->delete($recipe->recipe_cover_picture);
            }

            // Delete the recipe from the database
            $recipe->delete();
            \Log::info('Recipe deleted successfully', [
                'recipe_id' => $id
            ]);

            return $this->sendResponse([], 'Recipe deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete recipe: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
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

            // Increment checked_qty and decrement inventory_total_qty
            $recipe->checked_qty += 1;
            $recipe->inventory_total_qty -= 1;
            $recipe->save();

            // Add S3 URL for response
            $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);

            return $this->sendResponse($recipe, 'Recipe checked out successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to checkout recipe: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
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

            // Decrement checked_qty and increment inventory_total_qty
            $recipe->checked_qty -= 1;
            $recipe->inventory_total_qty += 1;
            $recipe->save();

            // Add S3 URL for response
            $recipe->recipe_cover_picture = $this->getS3Url($recipe->recipe_cover_picture);

            return $this->sendResponse($recipe, 'Recipe returned successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to return recipe: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Failed to return recipe: ' . $e->getMessage(), [], 500);
        }
    }
}
