<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ReportsController;

// Health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Public API Routes (No authentication required)
Route::get('/rooms/{roomId}/booked-dates', [BookingController::class, 'getBookedDates'])->whereNumber('roomId');

// Protected API Routes (Session-based authentication for web)
// Using custom auth middleware that returns JSON for unauthenticated requests
Route::middleware(\App\Http\Middleware\AuthApi::class)->group(function () {
    // User Routes
    Route::get('/user', [LoginController::class, 'user']);
    Route::post('/logout', [LoginController::class, 'logout']);
    
    // User Profile endpoints
    Route::get('/user/profile', [UserController::class, 'showProfile']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);
    
    // User Management API
    Route::apiResource('users', UserController::class);
    
    // Room Management API
    Route::apiResource('rooms', RoomController::class);
    Route::patch('/rooms/{room}/status', [RoomController::class, 'updateStatus']);
    Route::get('/rooms-types', [RoomController::class, 'getTypes']);
    Route::get('/amenities', [RoomController::class, 'getAmenities']);
    
    // Booking Management API - Feedback routes MUST come before apiResource  
    Route::post('bookings/{bookingId}/feedback', [BookingController::class, 'submitFeedback'])->where('bookingId', '[0-9]+');
    Route::get('bookings/{bookingId}/feedback', [BookingController::class, 'getFeedback'])->where('bookingId', '[0-9]+');
    
    // Resource routes
    Route::apiResource('bookings', BookingController::class);
    Route::patch('bookings/{reservation}/status', [BookingController::class, 'updateStatus']);
    
    // User Bookings API
    Route::post('user/bookings', [BookingController::class, 'storeUserBooking']);
    Route::get('user/bookings', [BookingController::class, 'getUserBookings']);
    
    // Reports API
    Route::get('reports', [ReportsController::class, 'index']);
    Route::get('reports/summary', [ReportsController::class, 'getSummary']);
    Route::get('reports/chart', [ReportsController::class, 'getBookingsChart']);
    Route::get('reports/bookings', [ReportsController::class, 'getBookingHistory']);
    Route::get('reports/payments', [ReportsController::class, 'getPaymentHistory']);
    Route::get('reports/months', [ReportsController::class, 'getAvailableMonths']);
    Route::get('user/reports', [ReportsController::class, 'getUserReports']);
    
    // Payments API
    Route::apiResource('payments', PaymentsController::class);
    Route::patch('payments/{reservation}/approve', [PaymentsController::class, 'approve']);
    Route::patch('payments/{reservation}/reject', [PaymentsController::class, 'reject']);
    Route::get('user/payments/pending', [PaymentsController::class, 'getPendingPayments']);
    Route::get('user/payments', [PaymentsController::class, 'getUserPayments']);
    Route::patch('user/payments/{reservation}', [PaymentsController::class, 'updatePaymentMethod']);
    Route::post('user/payments/{reservation}', [PaymentsController::class, 'submitPayment']);
    Route::get('user/payments/{reservation}', [PaymentsController::class, 'show']);
});