<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add a named route for login to prevent route not found errors
Route::get('/login', function() {
    return response()->json([
        'message' => 'This endpoint is for API use only. Please use /api/login for authentication.'
    ], 401);
})->name('login');
