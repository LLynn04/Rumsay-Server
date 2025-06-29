<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::middleware(['auth:sanctum', 'admin'])
    ->get('/users', [AuthController::class, 'allUsers']);



// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);




    // Routes that require email verification (except for admins)
    Route::middleware('verified')->group(function () {
        // Services (read-only for users)
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{service}', [ServiceController::class, 'show']);

        // Bookings (Cash payment only)
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

        // Admin only routes
        Route::middleware('admin')->group(function () {
            // Service CRUD
            Route::post('/services', [ServiceController::class, 'store']);
            Route::put('/services/{service}', [ServiceController::class, 'update']);
            Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

            // Booking management
            Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
            Route::patch('/bookings/{booking}/complete', [BookingController::class, 'completeBooking']);

            // Payment Management (Cash only)
            Route::patch('/bookings/{booking}/mark-payment-received', [BookingController::class, 'markPaymentReceived']);
            Route::get('/payments/pending', [BookingController::class, 'pendingPayments']);
            Route::get('/payments/summary', [BookingController::class, 'paymentSummary']);
        });
    });
});
