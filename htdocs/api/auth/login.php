<?php
// Include the bootstrap file which sets up database connection, constants, and other app configurations
require_once '../../config/bootstrap.php';

header('Content-Type: application/json');

// Check if the request method is POST (login credentials should be sent via POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Retrieve username and password from POST data
// Use trim to remove extra whitespace from username
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}


try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        $stmt->close();
        $conn->close();
        exit();
    }

    // Fetch user data as an associative array
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Verify the password using PHP's password_verify function
    // (Assumes passwords are hashed using password_hash)
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}
?>