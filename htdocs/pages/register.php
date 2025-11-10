<?php
require_once '../config/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Register";
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
                <img src="/assets/images/logo.png" alt="TechWave Logo">
            </div>
            
            <h2>Join TechWave</h2>
            <p class="auth-subtitle">Create your account and start sharing your ideas</p>
            
            <!-- Registration Form -->
            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Choose a username" required minlength="3" maxlength="50">
                    <small class="form-text">3-50 characters, letters, numbers, and underscores only</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           placeholder="Enter your email" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Create a password" required minlength="6">
                    <small class="form-text">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>
            
            <a href="/pages/login.php" class="btn btn-secondary-outline btn-block">
                Sign In
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>