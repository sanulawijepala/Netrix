<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token. Please try again.";
    }
    
    // Sanitize and validate inputs
    $username = sanitizeInput($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $full_name = sanitizeInput($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = in_array($_POST['user_type'], ['student', 'teacher']) ? $_POST['user_type'] : 'student';
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $errors[] = "Username must be 4-20 characters long and can only contain letters, numbers, and underscores.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = "Username or email already exists.";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = passwordHash($password);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, user_type) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $full_name, $user_type]);
            
            $success = true;
            // You might want to log the user in automatically here or redirect to login page
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
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
    <title>Register | <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        }
        
        .auth-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .auth-header p {
            opacity: 0.9;
            font-size: 14px;
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
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .user-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .user-type-option {
            flex: 1;
            text-align: center;
        }
        
        .user-type-option input[type="radio"] {
            display: none;
        }
        
        .user-type-option label {
            display: block;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-type-option input[type="radio"]:checked + label {
            border-color: var(--primary-color);
            background-color: rgba(79, 70, 229, 0.1);
        }
        
        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            height: 5px;
            background-color: #e2e8f0;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s;
        }
        
        .password-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
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
                <h1>Create Your Account</h1>
                <p>Join our learning community today</p>
            </div>
            
            <div class="auth-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Registration successful! You can now <a href="login.php" class="login-link">login</a>.
                    </div>
                <?php elseif (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="POST" id="registrationForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                               required>
                    </div>
                    
                    <div class="user-type-selector">
                        <div class="user-type-option">
                            <input type="radio" id="student" name="user_type" value="student" checked>
                            <label for="student">
                                <i class="fas fa-user-graduate"></i><br>
                                Student
                            </label>
                        </div>
                        <div class="user-type-option">
                            <input type="radio" id="teacher" name="user_type" value="teacher">
                            <label for="teacher">
                                <i class="fas fa-chalkboard-teacher"></i><br>
                                Teacher
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-hint">
                            Password must be at least 8 characters long
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn">Register</button>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php" class="login-link">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Contains numbers
            if (/\d/.test(password)) strength += 1;
            
            // Contains special chars
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            
            // Contains uppercase and lowercase
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Update strength bar
            const width = strength * 20;
            passwordStrengthBar.style.width = `${width}%`;
            
            // Update color
            if (strength <= 1) {
                passwordStrengthBar.style.backgroundColor = '#ef4444'; // red
            } else if (strength <= 3) {
                passwordStrengthBar.style.backgroundColor = '#f59e0b'; // orange
            } else {
                passwordStrengthBar.style.backgroundColor = '#10b981'; // green
            }
        });
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>