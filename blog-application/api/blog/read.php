<?php
require_once '../../config/bootstrap.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if it's a SINGLE blog request (has id parameter)
if(isset($_GET['id']) && !isset($_GET['limit'])) {
    $id = intval($_GET['id']);
    
    if($id <= 0){
        echo json_encode(['success'=>false,'message'=>'Invalid blog ID']);
        exit();
    }
    
    try{
        $conn = getDBConnection();
        
        if (!$conn) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit();
        }
        
        $stmt = $conn->prepare("
            SELECT bp.*, u.username, u.id as user_id, c.name as category,
                   (SELECT COUNT(*) FROM blog_likes WHERE blog_id = bp.id) as like_count,
                   (SELECT COUNT(*) > 0 FROM blog_likes WHERE blog_id = bp.id AND user_id = ?) as user_liked
            FROM blog_posts bp
            JOIN users u ON bp.user_id = u.id
            LEFT JOIN categories c ON bp.category_id = c.id
            WHERE bp.id = ?
        ");
        
        $current_user_id = $_SESSION['user_id'] ?? 0;
        $stmt->bind_param("ii", $current_user_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 0){
            echo json_encode(['success'=>false,'message'=>'Blog not found']);
            $stmt->close();
            $conn->close();
            exit();
        }
        
        $blog = $result->fetch_assoc();
        
        // Fix image path if empty
        if(empty($blog['image'])){
            $blog['image'] = '/blog-application/assets/images/blog-placeholder.jpg';
        }
        
        echo json_encode(['success'=>true,'blog'=>$blog]);
        
        $stmt->close();
        $conn->close();
        exit();
        
    }catch(Exception $e){
        error_log("Read single blog error: ".$e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Failed to load blog']);
        exit();
    }
}

// Otherwise, LIST blogs (your existing code)
try {
    $conn = getDBConnection();

    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);

if ($user_id) {
    $current_user = $_SESSION['user_id'] ?? 0;
    $query = "
        SELECT bp.id, bp.title, bp.content, bp.created_at, bp.updated_at, bp.image,
               c.name AS category_name,
               u.username, u.id AS author_id,
               SUBSTRING(bp.content, 1, 200) AS excerpt,
               (SELECT COUNT(*) FROM blog_likes WHERE blog_id = bp.id) AS like_count,
               (SELECT COUNT(*) > 0 FROM blog_likes WHERE blog_id = bp.id AND user_id = ?) AS user_liked
        FROM blog_posts bp
        INNER JOIN users u ON bp.user_id = u.id
        LEFT JOIN categories c ON bp.category_id = c.id
        WHERE bp.user_id = ?
        ORDER BY bp.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $current_user, $user_id, $limit, $offset);
} else {
    $current_user = $_SESSION['user_id'] ?? 0;
    $query = "
        SELECT bp.id, bp.title, bp.content, bp.created_at, bp.updated_at, bp.image,
               c.name AS category_name,
               u.username, u.id AS author_id,
               SUBSTRING(bp.content, 1, 200) AS excerpt,
               (SELECT COUNT(*) FROM blog_likes WHERE blog_id = bp.id) AS like_count,
               (SELECT COUNT(*) > 0 FROM blog_likes WHERE blog_id = bp.id AND user_id = ?) AS user_liked
        FROM blog_posts bp
        INNER JOIN users u ON bp.user_id = u.id
        LEFT JOIN categories c ON bp.category_id = c.id
        ORDER BY bp.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $current_user, $limit, $offset);
}

    $stmt->execute();
    $result = $stmt->get_result();

    $blogs = [];
    while ($row = $result->fetch_assoc()) {
        if (empty($row['image'])) {
            $row['image'] = '/blog-application/assets/images/blog-placeholder.jpg';
        }
        if (empty($row['category_name'])) {
            $row['category_name'] = 'Uncategorized';
        }
        $blogs[] = $row;
    }

    echo json_encode(['success' => true, 'blogs' => $blogs]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Blog read error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch blogs. Please try again.']);
}
?>


