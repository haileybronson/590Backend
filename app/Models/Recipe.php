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
        'user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
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
