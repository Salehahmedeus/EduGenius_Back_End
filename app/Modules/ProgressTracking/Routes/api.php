<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProgressTracking\Controllers\AnalyticsController;

Route::middleware('auth:api')->group(function () {
    // 1. Home Screen Data
    Route::get('dashboard/home', [AnalyticsController::class, 'home']);

    // 2. Detailed Analytics Data
    Route::get('dashboard/stats', [AnalyticsController::class, 'stats']);
});
