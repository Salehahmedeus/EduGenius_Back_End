<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure the storage directories exist in /tmp
$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0777, true);
    mkdir($storagePath . '/framework/views', 0777, true);
    mkdir($storagePath . '/framework/cache', 0777, true);
    mkdir($storagePath . '/framework/sessions', 0777, true);
    mkdir($storagePath . '/logs', 0777, true);
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set the storage path to /tmp
$app->useStoragePath($storagePath);

// Handle the request
$app->handleRequest(Request::capture());
