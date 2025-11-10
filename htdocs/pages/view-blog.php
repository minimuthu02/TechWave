<?php
require_once '../config/bootstrap.php';
require_once '../includes/header.php';

$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($blog_id <= 0) {
    redirect('/pages/index.php');
}
?>

<!-- Dashboard-style background overlay -->
<div class="dashboard-overlay"></div>

<!-- Full homepage background -->
<div class="homepage-background-layer"></div>

<!-- Soft white overlay across entire page -->
<div class="white-overlay-layer"></div>


<!-- Page transition wrapper -->
<main class="main-content page-transition page-content">
    <div class="background-wrapper">
         <?php include '../includes/homepage-background.php'; ?>
    </div>

    <div class="container">
        <article id="blogPost" class="blog-post">
            <div class="loading">Loading blog post...</div>
        </article>

        <section class="comments-section">
            <h2>Comments</h2>

            <?php if (isLoggedIn()): ?>
                <form id="commentForm" class="comment-form">
                    <textarea id="commentContent" placeholder="Write your comment..." required maxlength="1000"></textarea>
                    <button type="submit" class="btn-post-comment">Post Comment</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">Please <a href="/pages/login.php">login</a> to leave a comment.</p>
            <?php endif; ?>

            <div id="commentsList" class="comments-list">
                <div class="loading">Loading comments...</div>
            </div>
        </section>
    </div>
</main>

<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
const blogId = <?php echo $blog_id; ?>;
const currentUserId = <?php echo isLoggedIn() ? $_SESSION['user_id'] : 'null'; ?>;

async function loadBlog() {
    try {
        const response = await fetch(`/api/blog/read.php?id=${blogId}`);
        const data = await response.json();

        if (data.success) {
            const blog = data.blog;
            document.title = `${blog.title} - <?php echo APP_NAME; ?>`;

            let actionsHtml = '';
            if (currentUserId === blog.user_id) {
                actionsHtml = `
                    <div class="blog-actions">
                        <a href="/pages/edit-blog.php?id=${blog.id}" class="btn btn-secondary">Edit</a>
                        <button onclick="deleteBlog(${blog.id})" class="btn btn-danger">Delete</button>
                    </div>
                `;
            }

            let featuredImageHtml = '';
            if (blog.image) {
                featuredImageHtml = `
                    <div class="blog-featured-image">
                        <img src="${escapeHtml(blog.image)}" alt="${escapeHtml(blog.title)}" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 2rem;">
                    </div>
                `;
            }

            document.getElementById('blogPost').innerHTML = `
                <div class="blog-header">
                    <h1>${escapeHtml(blog.title)}</h1>
                    <div class="blog-meta">
                        <span class="author">By ${escapeHtml(blog.username)}</span>
                        <span class="date">Published: ${formatDate(blog.created_at)}</span>
                        ${blog.updated_at !== blog.created_at ? 
                            `<span class="date">Updated: ${formatDate(blog.updated_at)}</span>` : ''}
                        <span class="category">Category: ${escapeHtml(blog.category)}</span>
                    </div>
                    ${actionsHtml}
                </div>
                ${featuredImageHtml}
                <div class="blog-content">${marked.parse(blog.content)}</div>
            `;
        } else {
            document.getElementById('blogPost').innerHTML = 
                '<p class="error">Blog post not found.</p>';
        }
    } catch (error) {
        console.error('Error loading blog:', error);
        document.getElementById('blogPost').innerHTML = 
            '<p class="error">Failed to load blog post. Please try again later.</p>';
    }
}

async function loadComments() {
    try {
        const response = await fetch(`/api/blog/comments.php?blog_id=${blogId}`);
        const data = await response.json();
        const commentsList = document.getElementById('commentsList');

        if (data.success && data.comments.length > 0) {
            commentsList.innerHTML = data.comments.map(comment => `
                <div class="comment" data-comment-id="${comment.id}">
                    <div class="comment-header">
                        <span class="comment-author">${escapeHtml(comment.username)}</span>
                        <span class="comment-date">${formatDate(comment.created_at)}</span>
                        ${currentUserId === comment.user_id ? `
                            <button onclick="deleteComment(${comment.id})" class="btn-delete-comment">Delete</button>
                        ` : ''}
                    </div>
                    <div class="comment-content">${escapeHtml(comment.content)}</div>
                </div>
            `).join('');
        } else {
            commentsList.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
        }
    } catch (error) {
        console.error('Error loading comments:', error);
        document.getElementById('commentsList').innerHTML = 
            '<p class="error">Failed to load comments.</p>';
    }
}

<?php if (isLoggedIn()): ?>
document.getElementById('commentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const content = document.getElementById('commentContent').value.trim();

    if (!content) {
        alert('Comment cannot be empty');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('blog_id', blogId);
        formData.append('content', content);

        const response = await fetch('/api/blog/comments.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            document.getElementById('commentContent').value = '';
            loadComments();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error posting comment:', error);
        alert('Failed to post comment. Please try again.');
    }
});
<?php endif; ?>

async function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('comment_id', commentId);

        const response = await fetch('/api/blog/comments.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            loadComments();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
        alert('Failed to delete comment. Please try again.');
    }
}

async function deleteBlog(blogId) {
    if (!confirm('Are you sure you want to delete this blog? This action cannot be undone.')) return;

    try {
        const formData = new FormData();
        formData.append('id', blogId);

        const response = await fetch('/api/blog/delete.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            setTimeout(() => {
                window.location.href = '/pages/dashboard.php';
            }, 1500);
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error deleting blog:', error);
        alert('Failed to delete blog. Please try again.');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('en-US', options);
}

loadBlog();
loadComments();

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

