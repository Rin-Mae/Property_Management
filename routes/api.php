<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TORRequestController;

// Public API Routes (Authentication)
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected API Routes (Session-based authentication for web)
Route::middleware('auth:web')->group(function () {
    // User Routes
    Route::get('/user', [LoginController::class, 'user']);
    Route::post('/logout', [LoginController::class, 'logout']);

    // TOR Request Routes
    Route::apiResource('tor-requests', TORRequestController::class);
});
