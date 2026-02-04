<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AILearning\Controllers\AIServiceController;

Route::post('ai/ask-normal', [AIServiceController::class, 'askNormalAi']);


Route::middleware('auth:api')->group(function () {
    Route::post('ai/ask', [AIServiceController::class, 'ask']);
    Route::get('ai/chats', [AIServiceController::class, 'listChats']);
    Route::get('ai/history/{id}', [AIServiceController::class, 'history']); // Optional: Load specific chat messages
    Route::delete('ai/chats/{id}', [AIServiceController::class, 'deleteChat']);
});
