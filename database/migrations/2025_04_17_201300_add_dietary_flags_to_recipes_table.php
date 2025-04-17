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
        Schema::table('recipes', function (Blueprint $table) {
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->boolean('is_dairy_free')->default(false);
            $table->boolean('is_nut_free')->default(false);
            $table->integer('prep_time_minutes')->nullable();
            $table->integer('cook_time_minutes')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('is_vegan');
            $table->dropColumn('is_vegetarian');
            $table->dropColumn('is_gluten_free');
            $table->dropColumn('is_dairy_free');
            $table->dropColumn('is_nut_free');
            $table->dropColumn('prep_time_minutes');
            $table->dropColumn('cook_time_minutes');
            $table->dropColumn('difficulty');
        });
    }
};
