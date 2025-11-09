<?php
require_once '../config/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Login";
$includeAuthJS = true;
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/pages/dashboard.php');
}
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-box">
            <!-- Website Logo at top center -->
            <div class="auth-logo">
                <img src="/blog-application/assets/images/logo.png" alt="TechWave Logo">
            </div>
            
            <h2>Welcome Back</h2>
            <p class="auth-subtitle">Sign in to continue your blogging journey</p>
            
            <!-- Login Form -->
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="auth-divider">
                <span>Don't have an account?</span>
            </div>
            
            <a href="/blog-application/pages/register.php" class="btn btn-secondary-outline btn-block">
                Create Account
            </a>
            
            <div class="demo-info">
                <strong>Demo Mode:</strong> Contact the administrator for login access.
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>