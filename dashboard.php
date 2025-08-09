<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add your dashboard styles here */
        /* This would be similar to the login/register styles but extended for the dashboard */
    </style>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user['full_name']) ?></h1>
    <p>You are logged in as <?= htmlspecialchars($user['user_type']) ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>