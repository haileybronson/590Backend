<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RecipeController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\IngredientController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(UserController::class)->group(function(){
        Route::get('user', 'getUser');
        Route::post('user/upload_avatar', 'uploadAvatar');
        Route::delete('user/remove_avatar','removeAvatar');
        Route::post('user/send_verification_email','sendVerificationEmail');
        Route::post('user/change_email', 'changeEmail');
    });

    Route::controller(RecipeController::class)->group(function(){
        Route::get('recipes', 'index');
        Route::post('recipes', 'store');
        Route::post('recipes/{id}', 'update');
        Route::delete('recipes/{id}', 'destroy');
        Route::post('recipes/{id}/checkout', 'checkout');
        Route::post('recipes/{id}/return', 'return');
    });

    Route::controller(TagController::class)->group(function(){
        Route::get('tags', 'index');
        Route::get('tags/search', 'search');
        Route::post('tags', 'store');
        Route::put('tags/{id}', 'update');
        Route::delete('tags/{id}', 'destroy');
    });

    Route::controller(IngredientController::class)->group(function(){
        Route::get('ingredients', 'index');
        Route::get('ingredients/search', 'search');
        Route::post('ingredients', 'store');
        Route::put('ingredients/{id}', 'update');
        Route::delete('ingredients/{id}', 'destroy');
    });
});

