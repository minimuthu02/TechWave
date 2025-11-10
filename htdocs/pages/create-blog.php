<?php
require_once '../config/bootstrap.php';

$pageTitle = "Create Blog";
$includeBlogJS = true;
require_once '../includes/header.php';

requireAuth();
?>

<!-- SimpleMDE CSS for markdown editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">

<!-- Dashboard overlay -->
<div class="dashboard-overlay"></div>

<!-- Homepage-style background -->
<div class="homepage-background-layer"></div>

<!-- Soft white overlay -->
<div class="white-overlay-layer"></div>

<main class="main-content page-content page-transition">
    <div class="container">
        <div class="blog-editor">
            <h1>Create New Blog Post</h1>
            
            <!-- Blog Creation Form -->
            <form id="createBlogForm" class="blog-form" enctype="multipart/form-data">
                <!-- Title Input -->
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required maxlength="255" 
                           placeholder="Enter your blog title">
                </div>

                <!-- Category Selection -->
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category_id" id="category" required>
                        <?php
                        // Fetch categories dynamically from database
                        $conn = getDBConnection();
                        $catResult = $conn->query("SELECT id, name FROM categories ORDER BY name");
                        if($catResult && $catResult->num_rows > 0){
                            while($cat = $catResult->fetch_assoc()){
                                echo '<option value="'.(int)$cat['id'].'">'.htmlspecialchars($cat['name']).'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Featured Image Upload with modern styling -->
                <div class="form-group">
                    <label for="image">Featured Image (Optional)</label>
                    <input type="file" id="image" name="image" 
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small class="form-text">Accepted formats: JPG, PNG, GIF, WEBP. Max size: 5MB</small>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" style="margin-top:10px; display:none;">
                        <img id="previewImg" src="" alt="Preview" 
                             style="max-width:300px; max-height:200px; border-radius:8px;">
                    </div>
                </div>
                
                <!-- Content Editor (Markdown) -->
                <div class="form-group">
                    <label for="content">Content (Markdown supported)</label>
                    <textarea id="content" name="content"></textarea>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Publish Blog</button>
                    <a href="/pages/dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- SimpleMDE JS -->
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script>
// Initialize SimpleMDE markdown editor with improved toolbar visibility
const simplemde = new SimpleMDE({
    element: document.getElementById("content"),
    spellChecker: false,
    placeholder: "Write your blog content here... Markdown is supported!",
    toolbar: [
        "bold", "italic", "heading", "|", 
        "quote", "unordered-list", "ordered-list", "|", 
        "link", "image", "|", 
        "preview", "side-by-side", "fullscreen", "|", 
        "guide"
    ],
    status: ["lines", "words", "cursor"]
});

// Image preview functionality
document.getElementById('image').addEventListener('change', function(e){
    const file = e.target.files[0];
    const previewDiv = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if(file){
        // Validate file size (5MB max)
        if(file.size > 5*1024*1024){
            alert('File size must be less than 5MB');
            this.value = '';
            previewDiv.style.display = 'none';
            return;
        }
        
        // Validate file type
        const allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
        if(!allowed.includes(file.type)){
            alert('Invalid file type. Please upload JPG, PNG, GIF, or WEBP');
            this.value = '';
            previewDiv.style.display = 'none';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e){
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        previewDiv.style.display = 'none';
    }
});

// Form submission handler
document.getElementById('createBlogForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    
    // Get form values
    const title = document.getElementById('title').value.trim();
    const category_id = document.getElementById('category').value;
    const content = simplemde.value().trim();
    const imageFile = document.getElementById('image').files[0];

    // Validate required fields
    if(!title || !content || !category_id){
        alert('Please fill in all required fields');
        return;
    }

    // Validate title length
    if(title.length < 3){
        alert('Title must be at least 3 characters');
        return;
    }
    
    // Validate content length
    if(content.length < 10){
        alert('Content must be at least 10 characters');
        return;
    }

    try{
        // Prepare form data
        const formData = new FormData();
        formData.append('title', title);
        formData.append('category_id', category_id);
        formData.append('content', content);
        if(imageFile) formData.append('image', imageFile);

        // Submit to API
        const response = await fetch('/api/blog/create.php',{
            method:'POST',
            body: formData
        });

        const data = await response.json();
        
        if(data.success){
            alert(data.message);
            // Redirect to the newly created blog post
            setTimeout(()=>{ 
                window.location.href = `/pages/view-blog.php?id=${data.blog_id}`; 
            },1000);
        } else {
            alert(data.message);
        }

    } catch(err){
        console.error(err);
        alert('Failed to create blog. Please try again.');
    }
});
</script>

<!-- Page transition animation -->
<script>
document.addEventListener("DOMContentLoaded",()=>{
    const page=document.querySelector(".page-transition");
    if(page){ 
        setTimeout(()=>{ 
            page.classList.add("visible"); 
        },50);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
