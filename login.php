<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.html?welcome=" . urlencode($_SESSION['username']));
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token. Please try again.";
    }
    
    // Sanitize inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Check for login attempts
    if (checkLoginAttempts($pdo, $email)) {
        $errors[] = "Too many failed login attempts. Please try again later.";
    } else {
        // Validate inputs
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        
        // If no errors, attempt login
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && verifyPassword($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Update last login
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Clear login attempts
                    clearLoginAttempts($pdo, $email);
                    
                    // Remember me functionality
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 days
                        
                        $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
                        $stmt->execute([$token, $expiry, $user['id']]);
                        
                        setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', true, true);
                    }
                    
                    // Log activity
                    logActivity($pdo, $user['id'], 'User logged in');
                    
                    // Redirect to index.html with welcome message
                    header("Location: index.html?welcome=" . urlencode($user['username']));
                    exit();
                } else {
                    $errors[] = "Invalid email or password.";
                    recordLoginAttempt($pdo, $email, false);
                }
            } catch (PDOException $e) {
                $errors[] = "Login failed: " . $e->getMessage();
            }
        }
    }
}

// Generate new CSRF token for the form
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EduSyncHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6366f1;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark-color);
            line-height: 1.6;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .auth-container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        
        .auth-header p {
            opacity: 0.9;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }
        
        .auth-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .auth-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background-color: #f8fafc;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        .mb-3 {
            margin-bottom: 15px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .social-login {
            margin-top: 30px;
        }
        
        .social-login p {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            color: #64748b;
            font-size: 14px;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background-color: #e2e8f0;
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        
        .facebook {
            background-color: #3b5998;
        }
        
        .google {
            background-color: #db4437;
        }
        
        .twitter {
            background-color: #1da1f2;
        }
        
        .register-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle input {
            padding-right: 40px;
        }
        
        .toggle-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
        }
        
        .edusynchub-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .edusynchub-logo img {
            height: 50px;
        }
        
        @media (max-width: 576px) {
            .auth-card {
                border-radius: 0;
            }
            
            .auth-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Login to access your AI-powered learning dashboard</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="edusynchub-logo">
                    <img src="resources/EDUSYNCHUB.jpg" alt="EduSyncHub Logo">
                </div>
                
                <form action="login.php" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                               required autofocus>
                    </div>
                    
                    <div class="form-group password-toggle">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <span class="toggle-icon" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    
                    <div class="social-login">
                        <p>Or login with</p>
                        <div class="social-icons">
                            <a href="#" class="social-icon facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-icon google">
                                <i class="fab fa-google"></i>
                            </a>
                            <a href="#" class="social-icon twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php" class="register-link">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email) {
                e.preventDefault();
                alert('Email is required!');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                alert('Password is required!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>