<?php
// Simple Vercel front-controller that delegates to the existing index.php
chdir(__DIR__ . '/..');

// prefer env-config (database.php will read getenv)
if (!defined('SERVERLESS_PHP')) define('SERVERLESS_PHP', true);

// minimal error handler so Vercel returns 500 instead of raw stack
set_error_handler(function($errno, $errstr) {
    http_response_code(500);
    error_log("PHP error [$errno]: $errstr");
    echo "Server error";
    return true;
});

// bootstrap so include_path and BASE_PATH are set
require_once __DIR__ . '/../src/bootstrap.php';

// include your app entry (adjust if your main file is different)
$entry = BASE_PATH . '/index.php';
if (!file_exists($entry)) {
    http_response_code(500);
    echo "Missing application entry: index.php";
    exit;
}

// load the app
require $entry;
?>
