<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unit'];

    /**
     * The recipes that belong to the ingredient.
     */
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredient')
                    ->withPivot('quantity', 'notes')
                    ->withTimestamps();
    }
}
