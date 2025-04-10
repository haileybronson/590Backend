<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nutrition_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id')->unique(); // One-to-one relationship
            $table->integer('calories')->nullable();
            $table->float('protein')->nullable(); // in grams
            $table->float('carbs')->nullable(); // in grams
            $table->float('fat')->nullable(); // in grams
            $table->float('fiber')->nullable(); // in grams
            $table->float('sugar')->nullable(); // in grams
            $table->text('notes')->nullable(); // Any additional nutritional information
            $table->timestamps();

            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_infos');
    }
};
