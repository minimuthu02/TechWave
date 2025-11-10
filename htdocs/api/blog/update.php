<?php
require_once '../../config/bootstrap.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()){
    echo json_encode(['success'=>false,'message'=>'You must be logged in']);
    exit();
}

if($_SERVER['REQUEST_METHOD']!=='POST'){
    echo json_encode(['success'=>false,'message'=>'Invalid request method']);
    exit();
}

$blog_id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category_name = trim($_POST['category'] ?? 'Technology');
$user_id = $_SESSION['user_id'];

$errors = [];

if($blog_id <=0) $errors[] = 'Invalid blog ID';
if(!userOwnsBlog($user_id,$blog_id)) {
    echo json_encode(['success'=>false,'message'=>'You do not have permission to edit this blog']);
    exit();
}

if(empty($title)){
    $errors[]='Title is required';
} elseif(strlen($title)>255){
    $errors[]='Title must be less than 255 characters';
} elseif(strlen($title)<3){
    $errors[]='Title must be at least 3 characters';
}

if(empty($content)){
    $errors[]='Content is required';
} elseif(strlen($content)<10){
    $errors[]='Content must be at least 10 characters';
}

// Map category name to ID
$allowedCategories = [
    'Technology' => 1,
    'Science' => 2,
    'Engineering & Innovation' => 3,
    'Research & Discoveries' => 4,
    'Tech Trends' => 5,
    'Industry' => 6,
    'DIY & Tutorials' => 7
];

$category_id = $allowedCategories[$category_name] ?? 1;

// Handle image upload if provided
$image_path = null;
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
    $allowed_types = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
    $max_size = 5*1024*1024; // 5MB
    
    if(!in_array($_FILES['image']['type'],$allowed_types)){
        $errors[]='Invalid image type. Only JPG, PNG, GIF, WEBP allowed';
    }
    
    if($_FILES['image']['size']>$max_size){
        $errors[]='Image size must be less than 5MB';
    }
    
    if(empty($errors)){
        $upload_dir = '../../uploads/';
        if(!is_dir($upload_dir)){
            mkdir($upload_dir,0755,true);
        }
        
        $extension = pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
        $filename = 'blog_'.time().'_'.uniqid().'.'.$extension;
        $upload_path = $upload_dir.$filename;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'],$upload_path)){
            $image_path = '/uploads/'.$filename;
        } else {
            $errors[]='Failed to upload image';
        }
    }
}

if(!empty($errors)){
    echo json_encode(['success'=>false,'message'=>implode(', ',$errors)]);
    exit();
}

try{
    $conn = getDBConnection();
    
    // Update query - include image if provided
    if($image_path){
        $stmt = $conn->prepare("
            UPDATE blog_posts
            SET title=?, content=?, category_id=?, image=?, updated_at=CURRENT_TIMESTAMP
            WHERE id=?
        ");
        $stmt->bind_param("ssisi",$title,$content,$category_id,$image_path,$blog_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE blog_posts
            SET title=?, content=?, category_id=?, updated_at=CURRENT_TIMESTAMP
            WHERE id=?
        ");
        $stmt->bind_param("ssii",$title,$content,$category_id,$blog_id);
    }

    if($stmt->execute()){
        echo json_encode(['success'=>true,'message'=>'Blog updated successfully!']);
    } else {
        throw new Exception('Failed to update blog post');
    }

    $stmt->close();
    $conn->close();

}catch(Exception $e){
    error_log("Blog update error: ".$e->getMessage());
    echo json_encode(['success'=>false,'message'=>'Failed to update blog. Please try again.']);
}
?>

