<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PaymentsController;

// Landing page - root route (shows landing for everyone)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Prevent direct access to auth pages - guests are redirected to landing
Route::middleware('guest')->group(function () {
    // If a guest tries to access login/register via URL, they'll go through these routes
    // But the modals on landing.blade.php handle the actual form submissions
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
    
    // Redirect GET requests to /login and /register back to landing page
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
    
    // User Management
    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');
    
    // Client Management
    Route::get('/admin/clients', function () {
        return view('admin.clients');
    })->name('admin.clients');

    // Room Management
    Route::get('/admin/rooms', function () {
        return view('admin.rooms');
    })->name('admin.rooms');
    
    // Bookings Management
    Route::get('/admin/bookings', function () {
        return view('admin.bookings');
    })->name('admin.bookings');
    
    // Payments Management
    Route::get('/admin/payments', function () {
        return view('admin.payments');
    })->name('admin.payments');
    
    // Reports
    Route::get('/admin/reports', function () {
        return view('admin.reports');
    })->name('admin.reports');
    
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
});

// API Routes for User Management
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/user', [LoginController::class, 'user']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::patch('/rooms/{room}/status', [RoomController::class, 'updateStatus']);
    Route::get('/rooms-types', [RoomController::class, 'getTypes']);
    Route::get('/amenities', [RoomController::class, 'getAmenities']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::patch('/bookings/{reservation}/status', [BookingController::class, 'updateStatus']);
    
    // Reports endpoints
    Route::get('/reports/summary', [ReportsController::class, 'getSummary']);
    Route::get('/reports/chart', [ReportsController::class, 'getBookingsChart']);
    Route::get('/reports/bookings', [ReportsController::class, 'getBookingHistory']);
    Route::get('/reports/payments', [ReportsController::class, 'getPaymentHistory']);
    Route::get('/reports/months', [ReportsController::class, 'getAvailableMonths']);
    
    // Payments endpoints
    Route::get('/payments', [PaymentsController::class, 'index']);
    Route::get('/payments/{reservation}', [PaymentsController::class, 'show']);
    Route::patch('/payments/{reservation}/approve', [PaymentsController::class, 'approve']);
    Route::patch('/payments/{reservation}/reject', [PaymentsController::class, 'reject']);
});

// Catch-all redirect - guests trying to access protected routes go to landing
Route::fallback(function () {
    if (!auth('web')->check()) {
        return redirect()->route('landing');
    }
    return abort(404);
});