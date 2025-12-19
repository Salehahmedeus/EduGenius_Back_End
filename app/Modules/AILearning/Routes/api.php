<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AILearning\Controllers\AIServiceController;

Route::post('/ai-tutor/query', [AIServiceController::class, 'query']);
