<?php
/**
 * Main Configuration File
 * Note: env.php should be loaded BEFORE this file (via bootstrap.php)
 */

// Check if env() function exists (meaning env.php was loaded)
if (!function_exists('env')) {
    die('Error: env.php must be loaded before config.php. Use bootstrap.php to load configuration.');
}

// Database Configuration
define('DB_HOST', env('DB_HOST'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));
define('DB_NAME', env('DB_NAME'));

// App Configuration
define('APP_NAME', env('APP_NAME'));
define('APP_ENV', env('APP_ENV'));
define('APP_DEBUG', env('APP_DEBUG'));
define('SESSION_LIFETIME', env('SESSION_LIFETIME'));

// URL Configuration
define('BASE_URL', env('BASE_URL', 'https://techwave-blog.rf.gd'));
define('SITE_ROOT', dirname(__DIR__));

// Security Configuration
define('SECRET_KEY', env('SECRET_KEY', 'change-this-in-production'));

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    session_name(env('SESSION_NAME', 'techwave_session'));
    
    // Security settings for cookies
    if (env('SECURE_COOKIES', false)) {
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
    }
    
    session_start();
}

// Error Reporting Configuration
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../error_log.txt');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

/**
 * Generate URL from path
 * @param string $path - Path relative to base URL
 * @return string - Complete URL
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Redirect to a path
 * @param string $path - Path to redirect to
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

/**
 * Generate asset URL
 * @param string $path - Path to asset file
 * @return string - Complete asset URL
 */
function asset($path) {
    $path = ltrim($path, '/');
    return BASE_URL . '/assets/' . $path;
}

/**
 * Sanitize input data
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication
 * Redirects to login if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        redirect('pages/login.php');
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Set flash message
 * @param string $message
 * @param string $type - success, error, warning, info
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Truncate text
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . $suffix;
    }
    return $text;
}
?>