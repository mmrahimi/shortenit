<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShortUrl\RedirectShortUrlController;
use App\Http\Controllers\ShortUrl\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.token')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:auth');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/urls', [ShortUrlController::class, 'store'])->middleware('throttle:create-url');
    Route::get('/urls', [ShortUrlController::class, 'index'])->middleware('throttle:general');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum', 'throttle:general');

Route::get('/{shortCode}', RedirectShortUrlController::class)->name('redirect')->middleware('throttle:redirect');
