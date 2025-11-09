<?php
require_once '../../config/bootstrap.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$blog_id = intval($_POST['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($blog_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
    exit();
}

if (!userOwnsBlog($user_id, $blog_id)) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this blog']);
    exit();
}

try {
    $conn = getDBConnection();

    // After verifying ownership but before deleting from database
    $stmt = $conn->prepare("SELECT image FROM blog_posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $blog_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $blog = $result->fetch_assoc();

        // Delete the blog post
        $deleteStmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ? AND user_id = ?");
        $deleteStmt->bind_param("ii", $blog_id, $user_id);

        if ($deleteStmt->execute()) {
            // Delete associated image file if exists
            if (!empty($blog['image'])) {
                $imagePath = __DIR__ . '/../../' . ltrim($blog['image'], '/');
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            echo json_encode(['success' => true, 'message' => 'Blog deleted successfully']);
        } else {
            throw new Exception('Failed to delete blog post');
        }

        $deleteStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Blog not found or access denied']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Blog delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to delete blog. Please try again.']);
}
?>
