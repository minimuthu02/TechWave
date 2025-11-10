<?php
/**
 * Get the currently logged-in user's data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $conn = getDBConnection();
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            logoutUser(); // Clear session properly
            return null;
        }

        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $user;
    } catch (Exception $e) {
        error_log("Error in getCurrentUser(): " . $e->getMessage());
        return null;
    }
}

/**
 * Log out user (clears session fully)
 */
function logoutUser() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if a user owns a blog post
 */
function userOwnsBlog($user_id, $blog_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return false;
        }

        $blog = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $blog['user_id'] == $user_id;
    } catch (Exception $e) {
        error_log("Error in userOwnsBlog(): " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a user owns a comment
 */
function userOwnsComment($user_id, $comment_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return false;
        }

        $comment = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $comment['user_id'] == $user_id;
    } catch (Exception $e) {
        error_log("Error in userOwnsComment(): " . $e->getMessage());
        return false;
    }
}

/**
 * Require login to access a page
 */
function requireLogin($redirect_url = '/pages/login.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect($redirect_url);
    }
}

/**
 * Escape output for HTML safety
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
