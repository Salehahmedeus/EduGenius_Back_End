<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProgressTracking\Controllers\AnalyticsController;

Route::get('/dashboard', [AnalyticsController::class, 'index']);
