<?php
// Bootstrap the application
require_once __DIR__ . '/config/bootstrap.php';

// Redirect to the main homepage
header('Location: ' . url('pages/index.php'));
exit();
?>


