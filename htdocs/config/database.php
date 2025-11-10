<?php
/**
 * Database Configuration and Connection Handler
 */

// Ensure config is loaded first
if (!defined('DB_HOST')) {
    die('Configuration not loaded. Please load config.php first.');
}

/**
 * Get Database Connection
 * Returns a MySQLi connection object
 */
function getDBConnection() {
    // Create connection without error suppression in development
    if (APP_ENV === 'production') {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }
    
    // Check connection
    if ($conn->connect_error) {
        $errorMsg = 'Database connection failed: ' . $conn->connect_error;
        error_log($errorMsg);
        
        if (APP_DEBUG) {
            die(json_encode([
                'success' => false,
                'message' => $errorMsg,
                'debug' => [
                    'host' => DB_HOST,
                    'user' => DB_USER,
                    'database' => DB_NAME,
                    'error' => $conn->connect_error,
                    'errno' => $conn->connect_errno
                ]
            ]));
        } else {
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please try again later.'
            ]));
        }
    }
    
    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error setting charset: " . $conn->error);
        if (APP_DEBUG) {
            die(json_encode([
                'success' => false,
                'message' => 'Error setting database charset: ' . $conn->error
            ]));
        }
    }
    
    return $conn;
}

/**
 * Test database connection
 * @return bool
 */
function testDBConnection() {
    try {
        $conn = getDBConnection();
        if ($conn && $conn->ping()) {
            $conn->close();
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Database test failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Close database connection
 * @param mysqli $conn
 */
function closeDBConnection($conn) {
    if ($conn && $conn instanceof mysqli) {
        $conn->close();
    }
}

/**
 * Execute a query safely
 * @param mysqli $conn
 * @param string $query
 * @return mysqli_result|bool
 */
function executeQuery($conn, $query) {
    $result = $conn->query($query);
    
    if (!$result && APP_DEBUG) {
        error_log("Query Error: " . $conn->error);
        error_log("Query: " . $query);
    }
    
    return $result;
}

/**
 * Prepare statement helper
 * @param mysqli $conn
 * @param string $query
 * @return mysqli_stmt|false
 */
function prepareStatement($conn, $query) {
    $stmt = $conn->prepare($query);
    
    if (!$stmt && APP_DEBUG) {
        error_log("Prepare Error: " . $conn->error);
        error_log("Query: " . $query);
    }
    
    return $stmt;
}

/**
 * Get last insert ID
 * @param mysqli $conn
 * @return int
 */
function getLastInsertId($conn) {
    return $conn->insert_id;
}
?>