<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TORRequestController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated Routes (Session-based)
Route::middleware('auth')->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // Student Dashboard
    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
    
    // Main Dashboard - redirects based on role
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('student.dashboard');
    })->name('dashboard');
    
    // TOR Request Routes
    Route::get('/tor/create', [TORRequestController::class, 'create'])->name('tor.create');
    Route::get('/tor/requests', function () {
        return view('tor.requests');
    })->name('tor.requests');
    
    // Admin pending requests
    Route::get('/admin/pending-requests', function () {
        return view('admin.pending-requests');
    })->name('admin.pending-requests');
    
    // Admin all requests
    Route::get('/admin/all-requests', function () {
        return view('admin.all-requests');
    })->name('admin.all-requests');
    
    // Admin processing requests
    Route::get('/admin/processing', function () {
        return view('admin.processing-requests');
    })->name('admin.processing');
});

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth('web')->check() ? redirect()->route('dashboard') : redirect()->route('login');
});