<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.token')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/urls', [ShortUrlController::class, 'store']);
    Route::get('/urls/{shortCode}', [ShortUrlController::class, 'redirect']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
