<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AILearning\Controllers\AIServiceController;

Route::middleware('auth:api')->group(function () {
    Route::post('ai/ask', [AIServiceController::class, 'ask']);
    Route::get('ai/history', [AIServiceController::class, 'history']);
});
