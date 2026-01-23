<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Assessment\Controllers\QuizController;

Route::middleware('auth:api')->group(function () {
    // 1. Generate a new Quiz (AI)
    Route::post('quiz/generate', [QuizController::class, 'generate']);

    // 2. Submit Answers & Get Score
    Route::post('quiz/submit', [QuizController::class, 'submit']);

    // 3. Get list of all quizzes
    Route::get('quiz/all', [QuizController::class, 'index']);

    Route::get('quiz/{id}', [QuizController::class, 'show']);

    Route::get('quiz/result/{id}', [QuizController::class, 'showResult']);
});
