// Blog management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Blog JS loaded successfully');
    attachDeleteHandlers();
});

/**
 * Attach delete handlers to blog delete buttons
 * (used on dashboard or blog list page)
 */
function attachDeleteHandlers() {
    const deleteButtons = document.querySelectorAll('.delete-blog');
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function () {
            const blogId = this.dataset.id;
            if (confirm('Are you sure you want to delete this blog?')) {
                try {
                    const response = await fetch(`/api/blog/delete.php?id=${blogId}`, {
                        method: 'DELETE'
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('Blog deleted successfully!');
                        window.location.reload();
                    } else {
                        alert('Error deleting blog: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Something went wrong while deleting the blog.');
                }
            }
        });
    });
}

/**
 * Utility function for creating or updating blogs via API
 * (used on create-blog.php or edit-blog.php)
 * blogData should include: title, content, category, and optionally image (as FormData)
 */
async function saveBlog(blogData, isUpdate = false) {
    const endpoint = isUpdate
        ? '/api/blog/update.php'
        : '/api/blog/create.php';

    try {
        const isFormData = blogData instanceof FormData;

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: isFormData ? undefined : { 'Content-Type': 'application/json' },
            body: isFormData ? blogData : JSON.stringify(blogData)
        });

        const result = await response.json();

        if (result.success) {
            alert(isUpdate ? 'Blog updated!' : 'Blog created!');
            const redirectId = result.blog_id || blogData.id;
            window.location.href = `/pages/view-blog.php?id=${redirectId}`;
        } else {
            alert(result.message || 'Error saving blog.');
        }
    } catch (err) {
        console.error('Error saving blog:', err);
        alert('Server error while saving blog.');
    }
}

// Additional blog-specific features can be added here
// e.g., markdown rendering, live preview, autosave, etc.



