<?php
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function passwordHash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function logActivity($pdo, $user_id, $activity) {
    try {
        $stmt = $pdo->prepare("INSERT INTO user_activities (user_id, activity, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $activity, $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        error_log("Activity log failed: " . $e->getMessage());
    }
}

function checkLoginAttempts($pdo, $email) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                              WHERE email = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([$email, LOGIN_ATTEMPT_TIMEOUT]);
        $result = $stmt->fetch();
        
        return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
    } catch (PDOException $e) {
        error_log("Login attempts check failed: " . $e->getMessage());
        return false; // Fail open - don't block if there's a database error
    }
}

function recordLoginAttempt($pdo, $email, $success) {
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (email, success, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$email, $success, $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        error_log("Failed to record login attempt: " . $e->getMessage());
    }
}

function clearLoginAttempts($pdo, $email) {
    try {
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
    } catch (PDOException $e) {
        error_log("Failed to clear login attempts: " . $e->getMessage());
    }
}