<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Assessment\Controllers\QuizController;

Route::post('/quiz/start', [QuizController::class, 'start']);
Route::post('/quiz/submit', [QuizController::class, 'submit']);
