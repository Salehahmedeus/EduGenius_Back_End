<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Helper to ensure directory exists
function ensure_dir($path)
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

// Ensure the storage and cache directories exist in /tmp
$tmpPath = '/tmp';
$storagePath = $tmpPath . '/storage';
$bootstrapCachePath = $tmpPath . '/bootstrap/cache';

ensure_dir($storagePath);
ensure_dir($storagePath . '/framework/views');
ensure_dir($storagePath . '/framework/cache');
ensure_dir($storagePath . '/framework/sessions');
ensure_dir($storagePath . '/logs');
ensure_dir($bootstrapCachePath);

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Set environment variables for caching paths explicitly to points to /tmp
// This forces Laravel to use the writable /tmp directory for all its caching needs
putenv("APP_SERVICES_CACHE={$bootstrapCachePath}/services.php");
putenv("APP_PACKAGES_CACHE={$bootstrapCachePath}/packages.php");
putenv("APP_CONFIG_CACHE={$bootstrapCachePath}/config.php");
putenv("APP_ROUTES_CACHE={$bootstrapCachePath}/routes.php");
putenv("APP_EVENTS_CACHE={$bootstrapCachePath}/events.php");

// Handle SQLite Database on Vercel
$sourceDb = __DIR__ . '/../database/database.sqlite';
$tempDb = $tmpPath . '/database.sqlite';
if (!file_exists($tempDb) && file_exists($sourceDb)) {
    copy($sourceDb, $tempDb);
}
putenv("DB_DATABASE={$tempDb}");
putenv("DB_CONNECTION=sqlite");

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set the storage path to /tmp
$app->useStoragePath($storagePath);

// Handle the request
$app->handleRequest(Request::capture());
