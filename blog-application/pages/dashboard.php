<?php
require_once '../config/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Dashboard";
require_once '../includes/header.php';

requireLogin();

$currentUser = getCurrentUser();
?>

<!-- Homepage-style background -->
<div class="homepage-background-layer"></div>

<!-- Soft white overlay -->
<div class="white-overlay-layer"></div>

<!-- MAIN WRAPPER ADDED HERE -->
<main class="main-content">
    <div class="container">
        <div class="dashboard">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Welcome Back, <?php echo e($currentUser['username']); ?>!</h1>
                <a href="/blog-application/pages/create-blog.php" class="btn btn-primary">Create New Blog</a>
            </div>
            
            <!-- Dashboard Content with Grid Layout -->
            <div class="dashboard-content">
                <h2>Your Blog Posts</h2>
                <div id="userBlogs" class="blog-list">
                    <div class="loading">Loading your blogs...</div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Load user blogs in card grid format
async function loadUserBlogs() {
    try {
        const userId = <?php echo $currentUser['id']; ?>;
        const response = await fetch(`/blog-application/api/blog/read.php?user_id=${userId}`);
        const data = await response.json();
        
        const blogContainer = document.getElementById('userBlogs');
        
        if (data.success && data.blogs.length > 0) {
            // Display blogs in card grid format like index page with images
            blogContainer.innerHTML = data.blogs.map(blog => {
                // Get image source - use placeholder if no image
                const imageSrc = blog.image || '/blog-application/assets/images/blog-placeholder.jpg';
                
                return `
                <div class="blog-item">
                    <!-- Blog Image -->
                    <div class="blog-item-image">
                        <img src="${imageSrc}" alt="${escapeHtml(blog.title)}">
                    </div>
                    
                    <!-- Blog Content -->
                    <div class="blog-item-content">
                        <div class="blog-item-header">
                            <h3>
                                <a href="/blog-application/pages/view-blog.php?id=${blog.id}">
                                    ${escapeHtml(blog.title)}
                                </a>
                            </h3>
                            <div class="blog-item-actions">
                                <a href="/blog-application/pages/edit-blog.php?id=${blog.id}" 
                                   class="btn btn-small btn-secondary">Edit</a>
                                <button onclick="deleteBlog(${blog.id})" 
                                        class="btn btn-small btn-danger">Delete</button>
                            </div>
                        </div>
                        <div class="blog-meta">
                            <span class="date">Created: ${formatDate(blog.created_at)}</span>
                            ${blog.updated_at !== blog.created_at ? 
                                `<span class="date">Updated: ${formatDate(blog.updated_at)}</span>` : ''}
                        </div>
                        <div class="blog-excerpt">${escapeHtml(blog.excerpt)}...</div>
                    </div>
                </div>
            `}).join('');
        } else {
            blogContainer.innerHTML = '<p class="no-blogs">You haven\'t created any blogs yet. <a href="../pages/create-blog.php">Create your first blog!</a></p>';
        }
    } catch (error) {
        console.error('Error loading blogs:', error);
        document.getElementById('userBlogs').innerHTML = 
            '<p class="error">Failed to load blogs. Please try again later.</p>';
    }
}

// Delete blog function
async function deleteBlog(blogId) {
    if (!confirm('Are you sure you want to delete this blog? This action cannot be undone.')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('id', blogId);
        
        const response = await fetch('/blog-application/api/blog/delete.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            loadUserBlogs(); // Reload the blogs
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting blog:', error);
        showNotification('Failed to delete blog. Please try again.', 'error');
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('en-US', options);
}

// Load blogs on page load
loadUserBlogs();
</script>

<?php require_once '../includes/footer.php'; ?>