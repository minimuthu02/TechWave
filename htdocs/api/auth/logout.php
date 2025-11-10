<?php

require_once '../../config/bootstrap.php'; 
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// Destroy session cookie (for extra safety)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a new session to prevent errors on redirected page
session_start();

// Redirect to the correct blog application index page
header("Location: /pages/index.php");
exit();
?>