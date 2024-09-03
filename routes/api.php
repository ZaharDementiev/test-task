<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/product/user', [ProductController::class, 'byUser']);
    Route::get('/product/popular', [ProductController::class, 'popular']);
    Route::resource('tasks', TaskController::class)->except('create', 'edit');
});
