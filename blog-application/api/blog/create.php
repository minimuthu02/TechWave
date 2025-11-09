<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/bootstrap.php';

header('Content-Type: application/json');

// Log incoming request for debugging
file_put_contents(__DIR__.'/debug.log', "\n=== NEW REQUEST ".date('Y-m-d H:i:s')." ===\n", FILE_APPEND);
file_put_contents(__DIR__.'/debug.log', "POST: ".print_r($_POST,true)."\n", FILE_APPEND);
file_put_contents(__DIR__.'/debug.log', "FILES: ".print_r($_FILES,true)."\n", FILE_APPEND);
file_put_contents(__DIR__.'/debug.log', "SESSION: ".print_r($_SESSION,true)."\n", FILE_APPEND);

// Check if user is logged in
if(!isLoggedIn()){
    file_put_contents(__DIR__.'/debug.log', "❌ User not logged in\n", FILE_APPEND);
    echo json_encode(['success'=>false,'message'=>'You must be logged in to create a blog']);
    exit();
}
file_put_contents(__DIR__.'/debug.log', "✅ User logged in: ".$_SESSION['user_id']."\n", FILE_APPEND);

// Check request method
if($_SERVER['REQUEST_METHOD']!=='POST'){
    echo json_encode(['success'=>false,'message'=>'Invalid request method']);
    exit();
}

// Get and sanitize inputs
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$user_id = $_SESSION['user_id'];

$errors = [];

// Validate title
if(empty($title)){
    $errors[] = 'Title is required';
}elseif(strlen($title) < 3){
    $errors[] = 'Title must be at least 3 characters';
}elseif(strlen($title) > 255){
    $errors[] = 'Title must be less than 255 characters';
}

// Validate content
if(empty($content)){
    $errors[] = 'Content is required';
}elseif(strlen($content) < 10){
    $errors[] = 'Content must be at least 10 characters';
}

// Validate category_id
if($category_id <= 0){
    $errors[] = 'Invalid category selected';
}

if(!empty($errors)){
    echo json_encode(['success'=>false,'message'=>implode(', ',$errors)]);
    exit();
}

// Handle image upload
$imagePath = null;
if(isset($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK){
    file_put_contents(__DIR__.'/debug.log', "📸 Image upload detected\n", FILE_APPEND);

    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
    $allowedExtensions = ['jpg','jpeg','png','gif','webp'];
    $maxSize = 5*1024*1024; // 5MB

    if($file['size'] > $maxSize){
        echo json_encode(['success'=>false,'message'=>'File size must be less than 5MB']);
        exit();
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($extension,$allowedExtensions)){
        echo json_encode(['success'=>false,'message'=>'Invalid file type']);
        exit();
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo,$file['tmp_name']);
    finfo_close($finfo);

    if(!in_array($mimeType,$allowedTypes)){
        echo json_encode(['success'=>false,'message'=>'Invalid file MIME type']);
        exit();
    }

    $uploadDir = __DIR__.'/../../assets/uploads/blogs/';
    if(!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

    $filename = uniqid('blog_',true).'.'.$extension;
    $uploadPath = $uploadDir.$filename;

    if(move_uploaded_file($file['tmp_name'],$uploadPath)){
        $imagePath = '/blog-application/assets/uploads/blogs/'.$filename;
    }else{
        echo json_encode(['success'=>false,'message'=>'Failed to upload image']);
        exit();
    }
}elseif(isset($_FILES['image']) && $_FILES['image']['error']!==UPLOAD_ERR_NO_FILE){
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    $errorCode = $_FILES['image']['error'];
    $errorMessage = $uploadErrors[$errorCode] ?? 'Unknown upload error';
    echo json_encode(['success'=>false,'message'=>$errorMessage]);
    exit();
}

// Insert blog into database
try{
    $conn = getDBConnection();
    if(!$conn){
        echo json_encode(['success'=>false,'message'=>'Database connection failed']);
        exit();
    }

    if($imagePath){
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, category_id, title, content, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss",$user_id,$category_id,$title,$content,$imagePath);
    }else{
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, category_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiss",$user_id,$category_id,$title,$content);
    }

    if($stmt->execute()){
        $blog_id = $stmt->insert_id;
        echo json_encode([
            'success'=>true,
            'message'=>'Blog created successfully!',
            'blog_id'=>$blog_id,
            'image'=>$imagePath,
            'category_id'=>$category_id
        ]);
    }else{
        if($imagePath && file_exists(__DIR__.'/../../'.ltrim($imagePath,'/'))){
            unlink(__DIR__.'/../../'.ltrim($imagePath,'/'));
        }
        echo json_encode(['success'=>false,'message'=>'Failed to insert blog: '.$stmt->error]);
    }

    $stmt->close();
    $conn->close();

}catch(Exception $e){
    if($imagePath && file_exists(__DIR__.'/../../'.ltrim($imagePath,'/'))){
        unlink(__DIR__.'/../../'.ltrim($imagePath,'/'));
    }
    echo json_encode(['success'=>false,'message'=>'Failed to create blog: '.$e->getMessage()]);
}
?>

