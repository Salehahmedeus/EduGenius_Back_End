<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('otp/send', [AuthController::class, 'sendOtp']);
Route::post('otp/verify', [AuthController::class, 'verifyOtp']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', function() {
        return auth('api')->user();
    });
});