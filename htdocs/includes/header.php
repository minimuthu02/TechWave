<?php
// Load config and helper functions
require_once __DIR__ . '/functions.php';
$currentUser = getCurrentUser();

// Get total blog posts count
$totalPosts = 0;
try {
    require_once __DIR__ . '/../config/database.php';
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) as total FROM blog_posts");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalPosts = (int)$row['total'];
    }
    $conn->close();
} catch (Exception $e) {
    error_log("Error getting total posts: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? e($pageDescription) : 'A modern tech blog'; ?>">
    <title>
        <?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>
        <?php echo defined('APP_NAME') ? APP_NAME : 'TechWave'; ?>
    </title>

    <!-- Main Styles -->
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">

    <!-- Markdown Editor (optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo url('pages/index.php'); ?>">
                    <img src="<?php echo asset('images/logo.png'); ?>" alt="TechWave Logo" class="nav-logo">
                    <?php echo defined('APP_NAME') ? APP_NAME : 'TechWave'; ?>
                </a>
            </div>

            <button class="nav-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo url('pages/index.php'); ?>">Home</a></li>

                <!-- Category Dropdown with Total Posts Count -->
                <li class="category-dropdown-wrapper">
                    <button id="categoriesBtn">Categories</button>
                    <div id="categoryDropdown" class="category-dropdown">
                        <div class="category-dropdown-header">
                            Browse Categories
                            <span class="total-posts-badge"><?php echo $totalPosts; ?> Total Posts</span>
                        </div>
                        <div id="categoryList" class="category-list">
                            <div style="padding: 1rem; text-align: center;">Loading...</div>
                        </div>
                    </div>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo url('pages/dashboard.php'); ?>">My Blogs</a></li>
                    <li><a href="<?php echo url('pages/create-blog.php'); ?>">Create Blog</a></li>

                    <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                       <li><a href="<?php echo url('pages/admin-featured.php'); ?>">Manage Featured</a></li>
                    <?php endif; ?>
                    <li class="user-info-item">
                        <span class="user-info">Welcome, <?php echo e($currentUser['username']); ?></span>
                    </li>
                    <li><a href="<?php echo url('api/auth/logout.php'); ?>" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo url('pages/login.php'); ?>">Login</a></li>
                    <li><a href="<?php echo url('pages/register.php'); ?>" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div id="notification" class="notification"></div>
