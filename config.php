<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'education_portal');

// Establish database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Website configuration
define('SITE_NAME', 'EduPortal');
define('SITE_URL', 'http://localhost/education_portal');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5); // Maximum allowed login attempts
define('LOGIN_ATTEMPT_TIMEOUT', 15); // Minutes to lock after too many attempts

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false, // Set to true if using HTTPS
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Include functions
require_once 'functions.php';
?>