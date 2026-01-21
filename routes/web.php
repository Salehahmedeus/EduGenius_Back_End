<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-routes', function () {
    $modules = [
        'Authentication',
        'AILearning',
        'Assessment',
        'ContentManagement',
        'ProgressTracking'
    ];

    $log = [];

    // 1. Check Module Files
    foreach ($modules as $module) {
        // Try multiple path strategies
        $paths = [
            'base_path' => base_path("app/Modules/$module/Routes/api.php"),
            'relative' => __DIR__ . "/../app/Modules/$module/Routes/api.php",
            'real_path' => realpath(__DIR__ . "/../app/Modules/$module/Routes/api.php"),
        ];

        $status = [];
        foreach ($paths as $key => $p) {
            $status[$key] = [
                'path' => $p,
                'exists' => file_exists($p)
            ];
        }
        $log[$module] = $status;
    }

    // 2. Register Routes
    $routeCollection = Route::getRoutes();
    $routes = [];
    foreach ($routeCollection as $route) {
        $routes[] = $route->uri() . ' [' . implode('|', $route->methods()) . ']';
    }

    return response()->json([
        'debug_log' => $log,
        'registered_routes' => $routes,
        'base_path' => base_path(),
        'app_path' => app_path(),
    ]);
});
