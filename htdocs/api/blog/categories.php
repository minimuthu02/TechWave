<?php
require_once '../../config/bootstrap.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();

    // ✅ Join with blog_posts to get accurate count
    $query = "
        SELECT 
            c.id, 
            c.name, 
            c.created_at,
            COUNT(bp.id) AS blog_count
        FROM categories c
        LEFT JOIN blog_posts bp ON bp.category_id = c.id
        GROUP BY c.id, c.name, c.created_at
        ORDER BY c.name ASC
    ";
    
    $result = $conn->query($query);

    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'blog_count' => (int)$row['blog_count'] // ✅ Cast to integer
            ];
        }
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch categories'
    ]);
}
?>

