<?php
require_once '../../config/bootstrap.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $blog_id = intval($_GET['blog_id'] ?? 0);
        
        if ($blog_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
            exit();
        }
        
        // ✅ Fixed: Check if blog exists in blog_posts table
        $check_stmt = $conn->prepare("SELECT id FROM blog_posts WHERE id = ?");
        $check_stmt->bind_param("i", $blog_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Blog post not found']);
            $check_stmt->close();
            $conn->close();
            exit();
        }
        $check_stmt->close();
        
        // Get comments for this blog post
        $stmt = $conn->prepare("
            SELECT c.*, u.username
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.blog_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = [
                'id' => (int)$row['id'],
                'blog_id' => (int)$row['blog_id'],
                'user_id' => (int)$row['user_id'],
                'username' => $row['username'],
                'content' => $row['content'],
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'comments' => $comments]);
        $stmt->close();
    }

    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to comment']);
            exit();
        }
        
        $action = $_POST['action'] ?? 'create';
        
        if ($action === 'create') {
            $blog_id = intval($_POST['blog_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');
            $user_id = $_SESSION['user_id'];
            
            if ($blog_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
                exit();
            }
            
            // ✅ Fixed: Check if blog exists in blog_posts table
            $check_stmt = $conn->prepare("SELECT id FROM blog_posts WHERE id = ?");
            $check_stmt->bind_param("i", $blog_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'Blog post not found']);
                $check_stmt->close();
                $conn->close();
                exit();
            }
            $check_stmt->close();
            
            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
                exit();
            }
            
            if (strlen($content) < 3) {
                echo json_encode(['success' => false, 'message' => 'Comment must be at least 3 characters']);
                exit();
            }
            
            if (strlen($content) > 1000) {
                echo json_encode(['success' => false, 'message' => 'Comment is too long (max 1000 characters)']);
                exit();
            }
            
            // Insert comment
            $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $blog_id, $user_id, $content);
            
            if ($stmt->execute()) {
                $comment_id = $stmt->insert_id;
                
                // Get the newly created comment with username
                $stmt2 = $conn->prepare("
                    SELECT c.*, u.username
                    FROM comments c
                    INNER JOIN users u ON c.user_id = u.id
                    WHERE c.id = ?
                ");
                $stmt2->bind_param("i", $comment_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $comment = $result->fetch_assoc();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Comment added successfully!', 
                    'comment' => [
                        'id' => (int)$comment['id'],
                        'blog_id' => (int)$comment['blog_id'],
                        'user_id' => (int)$comment['user_id'],
                        'username' => $comment['username'],
                        'content' => $comment['content'],
                        'created_at' => $comment['created_at']
                    ]
                ]);
                $stmt2->close();
            } else {
                throw new Exception('Failed to insert comment');
            }
            $stmt->close();
        }
        
        elseif ($action === 'delete') {
            $comment_id = intval($_POST['comment_id'] ?? 0);
            $user_id = $_SESSION['user_id'];
            
            if ($comment_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
                exit();
            }
            
            // Check ownership
            if (!userOwnsComment($user_id, $comment_id) && $_SESSION['role'] !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment']);
                exit();
            }
            
            $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->bind_param("i", $comment_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Comment deleted successfully!']);
            } else {
                throw new Exception('Failed to delete comment');
            }
            $stmt->close();
        }
    }

    $conn->close();
} catch (Exception $e) {
    error_log("Comment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to process comment. Please try again.']);
}
?>