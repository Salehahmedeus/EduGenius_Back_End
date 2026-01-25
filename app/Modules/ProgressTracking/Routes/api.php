<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProgressTracking\Controllers\AnalyticsController;

Route::middleware('auth:api')->group(function () {
    // The One Route to Rule Them All
    Route::get('dashboard/home', [AnalyticsController::class, 'home']);

    // The "Save Report" action
    Route::post('dashboard/report', [AnalyticsController::class, 'generateReport']);
});
