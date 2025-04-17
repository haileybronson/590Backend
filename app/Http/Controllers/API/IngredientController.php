<?php

namespace App\Http\Controllers\API;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends BaseController
{
    public function index()
    {
        $ingredients = Ingredient::all();
        return $this->sendResponse($ingredients, 'Ingredients retrieved successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $ingredients = Ingredient::where('name', 'like', "%{$query}%")->get();
        return $this->sendResponse($ingredients, 'Ingredients retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'unit' => 'required|string|max:50'
        ]);

        try {
            $ingredient = Ingredient::create([
                'name' => $request->name,
                'unit' => $request->unit
            ]);

            return $this->sendResponse($ingredient, 'Ingredient created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to create ingredient: ' . $e->getMessage(), [], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name,' . $id,
            'unit' => 'required|string|max:50'
        ]);

        try {
            $ingredient = Ingredient::findOrFail($id);
            $ingredient->name = $request->name;
            $ingredient->unit = $request->unit;
            $ingredient->save();

            return $this->sendResponse($ingredient, 'Ingredient updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update ingredient: ' . $e->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ingredient = Ingredient::findOrFail($id);

            // Check if ingredient is used in any recipes before deletion
            if ($ingredient->recipes()->count() > 0) {
                return $this->sendError('Cannot delete ingredient that is used in recipes.', [], 400);
            }

            $ingredient->delete();

            return $this->sendResponse([], 'Ingredient deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete ingredient: ' . $e->getMessage(), [], 500);
        }
    }
}
