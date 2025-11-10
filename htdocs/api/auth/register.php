<?php
// Include the bootstrap file which sets up database connection, constants, and other app configurations
require_once '../../config/bootstrap.php';

// Set response type to JSON (so the client expects JSON responses)
header('Content-Type: application/json');

// Only allow POST requests (registration data should come via POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Retrieve and sanitize input data from POST
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Initialize an array to store validation errors
$errors = [];


if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters';
} elseif (strlen($username) > 50) {
    $errors[] = 'Username must be less than 50 characters';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email must be less than 100 characters';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
} elseif (strlen($password) > 255) {
    $errors[] = 'Password is too long';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

// If there are validation errors, return them as a single JSON message
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

try {
    $conn = getDBConnection();

    // ----------- CHECK IF USERNAME EXISTS ---------------
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // ----------- HASH PASSWORD ---------------
    // Use PHP's password_hash function for secure password storage
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful! Redirecting to login...']);
    } else {
        throw new Exception('Failed to insert user');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>