<?php
require_once '../config/bootstrap.php';

$pageTitle = "Edit Blog";
$includeBlogJS = true;
require_once '../includes/header.php';



requireAuth();

$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($blog_id <= 0) {
    redirect('/blog-application/pages/dashboard.php');
}

if (!userOwnsBlog($_SESSION['user_id'], $blog_id)) {
    redirect('/blog-application/pages/dashboard.php');
}


// Fetch blog data
$conn = getDBConnection();
$query = "SELECT bp.*, u.username FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    redirect('/blog-application/pages/dashboard.php');
}

$blog = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!-- SimpleMDE CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">

<!-- Dashboard-style background overlay -->
<div class="dashboard-overlay"></div>

<!-- Homepage-style background -->
<div class="homepage-background-layer"></div>

<!-- Soft white overlay -->
<div class="white-overlay-layer"></div>


<!-- Page transition wrapper -->
<main class="main-content page-transition page-content">
    <div class="container">
        <div class="blog-editor">
            <h1>Edit Blog Post</h1>

            <form id="editBlogForm" class="blog-form">
                <input type="hidden" id="blog_id" value="<?php echo $blog_id; ?>">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required maxlength="255" value="<?php echo e($blog['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" required>
                        <?php
                        $categories = [
                            "Technology", "Science", "Engineering & Innovation",
                            "Research & Discoveries", "Tech Trends", "Industry", "DIY & Tutorials"
                        ];
                        foreach ($categories as $cat) {
                            $selected = ($blog['category'] === $cat) ? 'selected' : '';
                            echo "<option value=\"$cat\" $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Content (Markdown supported)</label>
                    <textarea id="content" name="content"><?php echo e($blog['content']); ?></textarea>
                </div>

                <!-- Featured Image Upload -->
<div class="form-group">
    <label for="image">Change Featured Image (Optional)</label>
    <input type="file" id="image" name="image" 
           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
    <small class="form-text">Leave empty to keep current image. Accepted: JPG, PNG, GIF, WEBP. Max: 5MB</small>
    
    <?php if(!empty($blog['image'])): ?>
    <div style="margin-top:10px;">
        <img src="<?php echo e($blog['image']); ?>" alt="Current image" 
             style="max-width:200px; border-radius:8px;">
    </div>
    <?php endif; ?>
</div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Blog</button>
                    <a href="/blog-application/pages/dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- SimpleMDE JS -->
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>

<script>
const blogId = <?php echo $blog_id; ?>;

const simplemde = new SimpleMDE({
    element: document.getElementById("content"),
    spellChecker: false,
    placeholder: "Edit your blog content here...",
    toolbar: ["bold", "italic", "heading", "|", "quote", "unordered-list", "ordered-list", "|",
              "link", "image", "|", "preview", "side-by-side", "fullscreen", "|", "guide"]
});

document.getElementById('editBlogForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const title = document.getElementById('title').value.trim();
    const category = document.getElementById('category').value;
    const content = simplemde.value().trim();
    const imageFile = document.getElementById('image').files[0];

    if (!title || !content || !category) {
        alert('Please fill in all required fields');
        return;
    }

    if (title.length < 3) {
        alert('Title must be at least 3 characters');
        return;
    }

    if (content.length < 10) {
        alert('Content must be at least 10 characters');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', blogId);
        formData.append('title', title);
        formData.append('category', category);
        formData.append('content', content);
        if(imageFile) formData.append('image', imageFile);

        const response = await fetch('/blog-application/api/blog/update.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            setTimeout(() => {
                window.location.href = `/blog-application/pages/view-blog.php?id=${blogId}`;
            }, 1500);
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error updating blog:', error);
        alert('Failed to update blog. Please try again.');
    }
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

