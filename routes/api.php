<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.token')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:auth');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/urls', [ShortUrlController::class, 'store'])->middleware('throttle:create-url');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum', 'throttle:general');

Route::get('/{shortCode}', [ShortUrlController::class, 'redirect'])->name('redirect')->middleware('throttle:redirect');
