<?php
// Prevent multiple includes
if (defined('APP_BOOTSTRAPPED')) {
    return;
}
define('APP_BOOTSTRAPPED', true);

// Load environment and database configuration
require_once __DIR__ . '/database.php';

// Load helper functions (if not already loaded by database.php)
if (file_exists(__DIR__ . '/../includes/functions.php')) {
    require_once __DIR__ . '/../includes/functions.php';
}
?>