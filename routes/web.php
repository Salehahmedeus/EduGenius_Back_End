<?php

use Illuminate\Support\Facades\Route;


use Illuminate\Support\Facades\DB;

Route::get('/db-test', function () {
    try {
        // Try to fetch the database name
        $dbName = DB::connection()->getDatabaseName();
        return "✅ Connected successfully to database: " . $dbName;
    } catch (\Exception $e) {
        return "❌ Connection Failed: " . $e->getMessage();
    }
});
