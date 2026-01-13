<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AILearning\Controllers\AIServiceController;

Route::middleware('auth:api')->group(function () {
    Route::post('ai/ask', [AIServiceController::class, 'ask']);
    // Route::get('ai/history', [AIServiceController::class, 'history']);
    Route::post('ai/ask-with-file', [AIServiceController::class, 'askWithFile']);
    Route::get('ai/chats', [AIServiceController::class, 'listChats']);
    Route::get('ai/history/{conversation_id}', [AIServiceController::class, 'history']); // Optional: Load specific chat messages
});
