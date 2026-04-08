<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PaymentsController;

// Landing page - root route (shows landing for everyone)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Auth routes - WITHOUT web middleware to avoid CSRF
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Guest only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return redirect()->route('landing');
    })->name('login');
    
    Route::get('/register', function () {
        return redirect()->route('landing');
    })->name('register');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated Routes (Session-based)
Route::middleware('auth')->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // User Dashboard
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');
    
    // User Management
    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');
    
    // Room Management
    Route::get('/admin/rooms', function () {
        return view('admin.rooms');
    })->name('admin.rooms');
    
    // Bookings Management
    Route::get('/admin/bookings', function () {
        return view('admin.bookings');
    })->name('admin.bookings');
    
    // User Bookings
    Route::get('/user/bookings', function () {
        return view('user.bookings');
    })->name('user.bookings');
    
    // User Payments
    Route::get('/user/payments', function () {
        return view('user.payments');
    })->name('user.payments');
    
    // User Reports
    Route::get('/user/reports', function () {
        return view('user.reports');
    })->name('user.reports');
    
    // User Profile
    Route::get('/user/profile', function () {
        return view('user.profile');
    })->name('user.profile');
    
    // Payments Management
    Route::get('/admin/payments', function () {
        return view('admin.payments');
    })->name('admin.payments');
    
    // Reports
    Route::get('/admin/reports', function () {
        return view('admin.reports');
    })->name('admin.reports');
    
    // Student Dashboard - Redirect to User Dashboard
    Route::get('/student/dashboard', function () {
        return redirect()->route('user.dashboard');
    })->name('student.dashboard');
    
    // Main Dashboard - redirects based on role
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');
});

// Note: API routes are handled in routes/api.php

// Catch-all redirect - guests trying to access protected routes go to landing
Route::fallback(function () {
    if (!auth('web')->check()) {
        return redirect()->route('landing');
    }
    return abort(404);
});