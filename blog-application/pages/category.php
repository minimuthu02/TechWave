<?php
require_once '../config/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    redirect('/pages/index.php');
}

require_once '../config/database.php';

try {
    $conn = getDBConnection();

    // Get category details
    $stmt = $conn->prepare("SELECT id, name, created_at FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $categoryResult = $stmt->get_result();

    if ($categoryResult->num_rows === 0) {
        redirect('/pages/index.php');
    }

    $category = $categoryResult->fetch_assoc();
    $stmt->close();

    $pageTitle = $category['name'];
    $pageDescription = 'Browse blogs in ' . $category['name'];

    // Get blogs in this category
    $query = "SELECT bp.*, u.username, c.name AS category_name 
              FROM blog_posts bp 
              JOIN users u ON bp.user_id = u.id 
              LEFT JOIN categories c ON bp.category_id = c.id 
              WHERE bp.category_id = ? 
              ORDER BY bp.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $blogs = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $blogs = [];
    error_log("Error fetching category blogs: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<!-- Dashboard-style background overlay -->
<div class="dashboard-overlay"></div>

<!-- Page transition wrapper -->
<main class="main-content page-transition page-content">
    <div class="container">
        <!-- Category Header -->
        <section class="category-header" style="text-align: center; padding: 3rem 0 2rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-primary);">
                <?php echo htmlspecialchars($category['name']); ?>
            </h1>
            <p style="margin-top: 1rem; color: var(--text-secondary);">
                <?php echo count($blogs); ?> blog<?php echo count($blogs) !== 1 ? 's' : ''; ?> in this category
            </p>
        </section>

        <!-- Blog Posts -->
        <section class="featured-blogs">
            <?php if (!empty($blogs)): ?>
                <div class="blog-cards-grid">
                    <?php foreach ($blogs as $blog): ?>
                        <div class="featured-card">
                            <div class="featured-card-image">
                                <?php 
                                $imageSrc = !empty($blog['image']) ? $blog['image'] : '/blog-application/assets/images/blog-placeholder.jpg';
                                ?>
                                <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                                <div class="featured-card-overlay">
                                    <span class="featured-card-category">
                                        <?php echo htmlspecialchars($blog['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="featured-card-content">
                                <h3 class="featured-card-title">
                                    <a href="/blog-application/pages/view-blog.php?id=<?php echo $blog['id']; ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                <p class="featured-card-meta">
                                    <span class="author">By <?php echo htmlspecialchars($blog['username']); ?></span>
                                    <span class="separator">•</span>
                                    <span class="date"><?php echo formatDate($blog['created_at']); ?></span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-posts-message">
                    <p>No blog posts in this category yet.</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="/blog-application/pages/create-blog.php" class="btn btn-primary">Create First Post</a>
                    <?php else: ?>
                        <a href="/blog-application/pages/index.php" class="btn btn-secondary">Browse All Blogs</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Back to Categories -->
        <div style="text-align: center; margin: 3rem 0;">
            <a href="/blog-application/pages/index.php" class="btn btn-secondary">← Back to All Blogs</a>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const page = document.querySelector(".page-transition");
    if (page) {
        setTimeout(() => {
            page.classList.add("visible");
        }, 50);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>