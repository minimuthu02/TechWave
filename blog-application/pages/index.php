<?php
require_once '../config/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Home";
$pageDescription = "Explore the latest in technology and science";
require_once '../includes/header.php';
require_once '../config/database.php';

$current_user_id = $_SESSION['user_id'] ?? 0;

try {
    $conn = getDBConnection();

    // Fetch FEATURED blog
    $featuredQuery = "
        SELECT 
            bp.id,
            bp.title,
            bp.content,
            bp.image,
            bp.created_at,
            bp.updated_at,
            u.username,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            (SELECT COUNT(*) FROM blog_likes WHERE blog_id = bp.id) AS like_count,
            (SELECT COUNT(*) > 0 FROM blog_likes WHERE blog_id = bp.id AND user_id = ?) AS user_liked
        FROM blog_posts bp
        JOIN users u ON bp.user_id = u.id
        LEFT JOIN categories c ON bp.category_id = c.id
        WHERE bp.is_featured = 1
        LIMIT 1
    ";

    $stmt = $conn->prepare($featuredQuery);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $featuredResult = $stmt->get_result();
    $featuredBlog = $featuredResult->fetch_assoc();
    $stmt->close();

    // Fetch all other blog posts (exclude featured)
    $blogQuery = "
        SELECT 
            bp.id,
            bp.title,
            bp.content,
            bp.image,
            bp.created_at,
            bp.updated_at,
            u.username,
            COALESCE(c.name, 'Uncategorized') AS category_name,
            (SELECT COUNT(*) FROM blog_likes WHERE blog_id = bp.id) AS like_count,
            (SELECT COUNT(*) > 0 FROM blog_likes WHERE blog_id = bp.id AND user_id = ?) AS user_liked
        FROM blog_posts bp
        JOIN users u ON bp.user_id = u.id
        LEFT JOIN categories c ON bp.category_id = c.id
        WHERE bp.is_featured = 0
        ORDER BY bp.created_at DESC
        LIMIT 6
    ";

    $stmt = $conn->prepare($blogQuery);
    $stmt->bind_param("i", $current_user_id);
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
    die("Database Error: " . $e->getMessage());
}
?>

<!-- Dashboard-style background overlay -->
<div class="dashboard-overlay"></div>

<!-- Page transition wrapper -->
<main class="main-content page-transition page-content">

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="hero-subtitle">Innovate. Discover. Inspire.</p>
            <h1 class="hero-title">TechWave</h1>
            <p class="hero-description">Riding the next wave of innovation and discovery</p>
            <div class="hero-cta">
                <?php if (!isLoggedIn()): ?>
                    <a href="/blog-application/pages/register.php" class="btn btn-primary">Get Started</a>
                <?php else: ?>
                    <a href="/blog-application/pages/create-blog.php" class="btn btn-primary">Create Blog</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <p class="welcome-text">
            Dive into the future of tech and science. Share ideas, spark innovation, and be part of the change.
        </p>
    </section>

    <!-- Featured Blog Section -->
    <?php if ($featuredBlog): 
        $featuredImage = !empty($featuredBlog['image']) 
            ? $featuredBlog['image'] 
            : '/blog-application/assets/images/blog-placeholder.jpg';
        $featuredExcerpt = substr(strip_tags($featuredBlog['content']), 0, 250) . '...';
        $featuredLikeCount = $featuredBlog['like_count'] ?? 0;
        $featuredUserLiked = $featuredBlog['user_liked'] ?? 0;
        $heartIcon = $featuredUserLiked ? '❤️' : '🤍';
    ?>
    <div class="featured-blog-wrapper">
        <div class="featured-blog">
            <div class="featured-blog-image">
                <img src="<?php echo htmlspecialchars($featuredImage); ?>" 
                     alt="<?php echo htmlspecialchars($featuredBlog['title']); ?>">
            </div>
            <div class="featured-blog-content">
                <span class="featured-badge">⭐ Featured Post</span>
                <h2><?php echo htmlspecialchars($featuredBlog['title']); ?></h2>
                <div class="featured-blog-meta">
                    <span><?php echo htmlspecialchars($featuredBlog['category_name']); ?></span>
                    <span class="separator">•</span>
                    <span>By <?php echo htmlspecialchars($featuredBlog['username']); ?></span>
                    <span class="separator">•</span>
                    <span><?php echo formatDate($featuredBlog['created_at']); ?></span>
                </div>
                <p class="featured-blog-excerpt"><?php echo htmlspecialchars($featuredExcerpt); ?></p>
                <div class="featured-blog-actions">
                    <button class="like-btn" 
                            data-blog-id="<?php echo $featuredBlog['id']; ?>"
                            data-user-liked="<?php echo $featuredUserLiked; ?>">
                        <span class="heart-icon"><?php echo $heartIcon; ?></span>
                        <span class="like-count"><?php echo $featuredLikeCount; ?></span>
                    </button>
                    <a href="/blog-application/pages/view-blog.php?id=<?php echo $featuredBlog['id']; ?>" 
                       class="btn-read-more">Read Full Article →</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Blogs Section -->
    <div class="container">
        <div class="section-divider">
            <h2 class="section-title">Latest Blog Posts</h2>
            <p class="section-subtitle">Explore fresh insights and innovations from our community</p>
        </div>

        <section class="featured-blogs">
            <?php if (!empty($blogs)): ?>
                <div class="blog-cards-grid">
                    <?php foreach ($blogs as $blog): 
                        $imageSrc = !empty($blog['image']) 
                            ? $blog['image'] 
                            : '/blog-application/assets/images/blog-placeholder.jpg';
                        $snippet = substr(strip_tags($blog['content']), 0, 200) . 
                                   (strlen(strip_tags($blog['content'])) > 200 ? '...' : '');
                        
                        $likeCount = $blog['like_count'] ?? 0;
                        $userLiked = $blog['user_liked'] ?? 0;
                        $heartIcon = $userLiked ? '❤️' : '🤍';
                    ?>
                        <div class="blog-card" data-blog-id="<?php echo $blog['id']; ?>">
                            <div class="blog-card-image">
                                <img src="<?php echo htmlspecialchars($imageSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($blog['title']); ?>">
                            </div>
                            <div class="blog-card-content">
                                <h3>
                                    <a href="/blog-application/pages/view-blog.php?id=<?php echo $blog['id']; ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                <div class="blog-meta">
                                    <span class="category-badge"><?php echo htmlspecialchars($blog['category_name']); ?></span>
                                    <span>By <?php echo htmlspecialchars($blog['username']); ?></span>
                                    <span class="separator">•</span>
                                    <span><?php echo formatDate($blog['created_at']); ?></span>
                                </div>
                                <p class="blog-snippet"><?php echo htmlspecialchars($snippet); ?></p>
                                <div class="blog-actions">
                                    <button class="like-btn" 
                                            data-blog-id="<?php echo $blog['id']; ?>"
                                            data-user-liked="<?php echo $userLiked; ?>">
                                        <span class="heart-icon"><?php echo $heartIcon; ?></span>
                                        <span class="like-count"><?php echo $likeCount; ?></span>
                                    </button>
                                    <a href="/blog-application/pages/view-blog.php?id=<?php echo $blog['id']; ?>" 
                                       class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-posts-message">
                    <p>No blog posts available yet. Be the first to share your insights!</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="/blog-application/pages/create-blog.php" class="btn btn-primary">Create Your First Post</a>
                    <?php else: ?>
                        <a href="/blog-application/pages/register.php" class="btn btn-primary">Get Started</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if (!isLoggedIn()): ?>
            <section class="cta-section">
                <h2>Ready to Share Your Ideas?</h2>
                <p>Join our community of innovators and start sharing your tech journey today.</p>
                <div class="cta-buttons">
                    <a href="/blog-application/pages/register.php" class="btn btn-primary">Get Started</a>
                    <a href="/blog-application/pages/login.php" class="btn btn-secondary-outline">Sign In</a>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attach like button handlers
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', async function() {
            <?php if (!isLoggedIn()): ?>
                if (confirm('You must be logged in to like posts. Go to login page?')) {
                    window.location.href = '/blog-application/pages/login.php';
                }
                return;
            <?php endif; ?>
            
            const blogId = this.dataset.blogId;
            
            try {
                const formData = new FormData();
                formData.append('blog_id', blogId);

                const res = await fetch('/blog-application/api/blog/like-blog.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();
                
                if (data.success) {
                    // Update like count
                    const likeCount = parseInt(data.likes);
                    this.querySelector('.like-count').textContent = likeCount;
                    
                    // Update heart icon based on action
                    const heartIcon = this.querySelector('.heart-icon');
                    if (data.action === 'liked') {
                        heartIcon.textContent = '❤️';
                        this.dataset.userLiked = '1';
                        this.classList.add('liked');
                        setTimeout(() => this.classList.remove('liked'), 600);
                    } else {
                        heartIcon.textContent = '🤍';
                        this.dataset.userLiked = '0';
                    }
                } else {
                    alert(data.message || 'Failed to like blog.');
                }
            } catch (err) {
                console.error(err);
                alert('Failed to like blog. Please try again.');
            }
        });
    });
});

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
