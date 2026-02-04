<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;

// Registration & Login
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');

// OTP routes
Route::post('otp/send', [AuthController::class, 'sendOtp']);
Route::post('otp/verify', [AuthController::class, 'verifyOtp']);


// Password Reset
Route::post('password/email', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('change_user_name', [AuthController::class, 'change_user_name']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', function () {
        return auth('api')->user();
    });
});
