<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'recipe_cover_picture',
        'inventory_total_qty',
        'checked_qty',
        'user_id',
        'is_vegan',
        'is_vegetarian',
        'is_gluten_free',
        'is_dairy_free',
        'is_nut_free',
        'prep_time_minutes',
        'cook_time_minutes',
        'difficulty'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'is_vegan' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_gluten_free' => 'boolean',
        'is_dairy_free' => 'boolean',
        'is_nut_free' => 'boolean',
        'prep_time_minutes' => 'integer',
        'cook_time_minutes' => 'integer'
    ];

    /**
     * Get the user that owns the recipe.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The tags that belong to the recipe.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recipe_tag');
    }

    /**
     * The ingredients that belong to the recipe.
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredient')
                    ->withPivot('quantity', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the nutrition information associated with the recipe.
     */
    public function nutritionInfo()
    {
        return $this->hasOne(NutritionInfo::class);
    }
}
