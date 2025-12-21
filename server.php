<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicPath = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($publicPath)) {
    return false; // Let the built-in server serve the requested file
}

require __DIR__ . '/public/index.php';
