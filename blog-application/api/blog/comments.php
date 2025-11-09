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
            $comments[] = $row;
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
            
            if (empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
                exit();
            }
            
            if (strlen($content) > 1000) {
                echo json_encode(['success' => false, 'message' => 'Comment is too long (max 1000 characters)']);
                exit();
            }
            
            $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $blog_id, $user_id, $content);
            
            if ($stmt->execute()) {
                $comment_id = $stmt->insert_id;
                
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
                
                echo json_encode(['success' => true, 'message' => 'Comment added!', 'comment' => $comment]);
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
            
            if (!userOwnsComment($user_id, $comment_id)) {
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