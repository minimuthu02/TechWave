<?php
require_once '../../config/bootstrap.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a blog']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$blog_id = intval($_POST['blog_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($blog_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if user already liked this blog
    $checkStmt = $conn->prepare("SELECT id FROM blog_likes WHERE blog_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $blog_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // User already liked - remove like (unlike)
        $deleteStmt = $conn->prepare("DELETE FROM blog_likes WHERE blog_id = ? AND user_id = ?");
        $deleteStmt->bind_param("ii", $blog_id, $user_id);
        $deleteStmt->execute();
        $deleteStmt->close();
        $action = 'unliked';
    } else {
        // User hasn't liked - add like
        $insertStmt = $conn->prepare("INSERT INTO blog_likes (blog_id, user_id) VALUES (?, ?)");
        $insertStmt->bind_param("ii", $blog_id, $user_id);
        $insertStmt->execute();
        $insertStmt->close();
        $action = 'liked';
    }
    
    $checkStmt->close();
    
    // Get updated like count
    $countStmt = $conn->prepare("SELECT COUNT(*) as like_count FROM blog_likes WHERE blog_id = ?");
    $countStmt->bind_param("i", $blog_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $likeCount = $countResult->fetch_assoc()['like_count'];
    $countStmt->close();
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'likes' => (int)$likeCount,
        'action' => $action,
        'message' => $action === 'liked' ? 'Blog liked!' : 'Blog unliked!'
    ]);
    
} catch (Exception $e) {
    error_log("Error toggling like: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to process like']);
}
?>
