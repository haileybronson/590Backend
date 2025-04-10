<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NutritionInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'calories',
        'protein',
        'carbs',
        'fat',
        'fiber',
        'sugar',
        'notes'
    ];

    /**
     * Get the recipe that owns the nutrition information.
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
