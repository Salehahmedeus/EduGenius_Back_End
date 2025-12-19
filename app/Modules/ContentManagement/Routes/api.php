<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ContentManagement\Controllers\FileController;

Route::post('/materials/upload', [FileController::class, 'upload']);
