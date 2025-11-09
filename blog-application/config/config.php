<?php
// Load environment variables
require_once __DIR__ . '/env.php';

// Define base URL from environment
define('BASE_URL', env('BASE_URL', 'http://localhost/blog-application'));
define('SITE_ROOT', dirname(__DIR__));

// App configuration
define('APP_NAME', env('APP_NAME', 'TechWave'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', true));

// Security
define('SECRET_KEY', env('SECRET_KEY', 'change-this-in-production'));

// Session configuration
ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', 7200));
session_name(env('SESSION_NAME', 'techwave_session'));

// Security settings
if (env('SECURE_COOKIES', false)) {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper function to generate URLs
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

// Helper function to redirect
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

// Helper function for assets
function asset($path) {
    $path = ltrim($path, '/');
    return BASE_URL . '/assets/' . $path;
}
?>