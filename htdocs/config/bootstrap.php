<?php
/**
 * bootstrap.php
 * Main app initialization file
 * 
 * Responsibilities:
 * 1. Start session
 * 2. Load environment variables (env.php)
 * 3. Load main configuration (config.php)
 * 4. Load helpers and functions
 * 5. Load database connection
 * 6. Define any global constants if needed
 */

// --------------------------
// 1. Start session
// --------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------
// 2. Load environment variables
// --------------------------
// Make sure the path is correct relative to bootstrap.php
require_once __DIR__ . '/env.php';

// --------------------------
// 3. Load main configuration
// --------------------------
require_once __DIR__ . '/config.php';

// --------------------------
// 4. Load helper functions
// --------------------------
if (file_exists(__DIR__ . '/helpers.php')) {
    require_once __DIR__ . '/helpers.php';
}

// --------------------------
// 5. Load database connection
// --------------------------
if (file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
}

// --------------------------
// 6. Optional: Define global constants
// --------------------------
define('BOOTSTRAP_LOADED', true);
