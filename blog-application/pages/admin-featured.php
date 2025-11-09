<?php
require_once '../config/bootstrap.php';
requireAuth();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    redirect('/pages/index.php');
}

$pageTitle = "Manage Featured Blog";
require_once '../includes/header.php';
require_once '../config/database.php';

$message = '';

// Handle feature/unfeature action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'])) {
    try {
        $conn = getDBConnection();
        $blog_id = intval($_POST['blog_id']);
        $action = $_POST['action'];
        
        if ($action === 'feature') {
            // Unfeature all blogs first (only one can be featured)
            $conn->query("UPDATE blog_posts SET is_featured = 0");
            
            // Feature the selected blog
            $stmt = $conn->prepare("UPDATE blog_posts SET is_featured = 1 WHERE id = ?");
            $stmt->bind_param("i", $blog_id);
            $stmt->execute();
            $stmt->close();
            $message = "Blog featured successfully!";
        } elseif ($action === 'unfeature') {
            $stmt = $conn->prepare("UPDATE blog_posts SET is_featured = 0 WHERE id = ?");
            $stmt->bind_param("i", $blog_id);
            $stmt->execute();
            $stmt->close();
            $message = "Blog unfeatured successfully!";
        }
        
        $conn->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Get all blogs
try {
    $conn = getDBConnection();
    $query = "
        SELECT bp.*, u.username, c.name as category_name
        FROM blog_posts bp
        JOIN users u ON bp.user_id = u.id
        LEFT JOIN categories c ON bp.category_id = c.id
        ORDER BY bp.is_featured DESC, bp.created_at DESC
    ";
    $result = $conn->query($query);
    $blogs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
    }
    $conn->close();
} catch (Exception $e) {
    $blogs = [];
    error_log("Error: " . $e->getMessage());
}
?>

<div class="dashboard-overlay"></div>

<main class="main-content page-transition page-content">
    <div class="container">
        <div class="dashboard-header">
            <h1>Manage Featured Blog</h1>
            <a href="/blog-application/pages/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="notification success show" style="position: relative; margin-bottom: 2rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-content">
            <h2>All Blog Posts</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                Only one blog can be featured at a time. The featured blog will appear in the hero section on the homepage.
            </p>

            <?php if (!empty($blogs)): ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="blog-item <?php echo (int)$blog['is_featured'] === 1 ? 'featured-item' : ''; ?>">
                        <div class="blog-item-header">
                            <div>
                                <h3>
                                    <a href="/blog-application/pages/view-blog.php?id=<?php echo $blog['id']; ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                    <?php if ((int)$blog['is_featured'] === 1): ?>
                                        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; margin-left: 0.5rem;">⭐ FEATURED</span>
                                    <?php endif; ?>
                                </h3>
                                <div class="blog-meta">
                                    <span>By <?php echo htmlspecialchars($blog['username']); ?></span>
                                    <span><?php echo htmlspecialchars($blog['category_name']); ?></span>
                                    <span><?php echo formatDate($blog['created_at']); ?></span>
                                </div>
                            </div>
                            <div class="blog-item-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                    <?php if ((int)$blog['is_featured'] === 1): ?>
                                        <input type="hidden" name="action" value="unfeature">
                                        <button type="submit" class="btn btn-secondary btn-small">Unfeature</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="feature">
                                        <button type="submit" class="btn btn-primary btn-small">Set as Featured</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        <div class="blog-excerpt">
                            <?php 
                            $excerpt = strip_tags($blog['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 150)) . '...';
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-blogs">No blogs available.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.featured-item {
    border: 2px solid #667eea !important;
    background: linear-gradient(145deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05)) !important;
}

/* Add spacing between blog items */
.blog-item {
    margin-bottom: 2rem !important;
    padding: 1.5rem !important;
    border-radius: 12px;
    background-color: var(--bg-card);
    box-shadow: none;
}

.blog-item:last-child {
    margin-bottom: 0 !important;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const page = document.querySelector(".page-transition");
    if (page) {
        setTimeout(() => { page.classList.add("visible"); }, 50);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>