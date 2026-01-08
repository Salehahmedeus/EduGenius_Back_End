<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ContentManagement\Controllers\FileController;

Route::middleware('auth:api')->group(function () {
    Route::post('materials/upload', [FileController::class, 'upload']);
    Route::get('materials', [FileController::class, 'list']);
});
