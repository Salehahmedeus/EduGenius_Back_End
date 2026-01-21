<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            $modules = [
                'Authentication',
                'AILearning',
                'Assessment',
                'ContentManagement',
                'ProgressTracking'
            ];

            foreach ($modules as $module) {
                // 👇 Method A: Use __DIR__ relative to bootstrap/app.php
                // bootstrap/ is one level deep, app/ is one level up.
                $path = __DIR__ . "/../app/Modules/$module/Routes/api.php";

                if (file_exists($path)) {
                    Route::prefix('api')
                        ->middleware('api')
                        ->group($path);
                }
                // Fallback: Log if missing (Only works if you have error logs enabled)
                else {
                    error_log("Failed to load route: $path");
                }
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
