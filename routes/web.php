<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;

Route::get('/debug-routes', function () {
    // 1. Clear Route Cache (Crucial for Vercel)
    Artisan::call('route:clear');

    // 2. Check if Module files exist
    $modules = ['Authentication', 'AILearning', 'Assessment', 'ContentManagement', 'ProgressTracking'];
    $debug = [];

    foreach ($modules as $module) {
        $path = base_path("app/Modules/$module/Routes/api.php");
        $debug[$module] = [
            'base_path' => ['path' => $path, 'exists' => file_exists($path)],
            // Try relative path just in case
            'relative' => ['path' => __DIR__ . '/../app/Modules/' . $module . '/Routes/api.php', 'exists' => file_exists(__DIR__ . '/../app/Modules/' . $module . '/Routes/api.php')],
            'real_path' => ['path' => realpath(base_path("app/Modules/$module/Routes/api.php")), 'exists' => (bool)realpath(base_path("app/Modules/$module/Routes/api.php"))]
        ];
    }

    // 3. List all currently registered routes
    $registeredRoutes = [];
    foreach (Route::getRoutes() as $route) {
        $registeredRoutes[] = $route->uri() . ' [' . implode('|', $route->methods()) . ']';
    }

    return [
        'debug_log' => $debug,
        'registered_routes' => $registeredRoutes,
        'base_path' => base_path(),
        'app_path' => app_path(),
    ];
});
