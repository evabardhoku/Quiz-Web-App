<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quiz Platform</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
        }
        header {
            background-color: #1f2937;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            font-size: 1.5rem;
            margin: 0;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            font-weight: 500;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 2rem;
        }
    </style>
</head>
<body>
<header>
    <h1>🎓 UniQuiz</h1>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'professor'): ?>
                <a href="/professor/dashboard.php">Dashboard</a>
            <?php elseif ($_SESSION['role'] === 'student'): ?>
                <a href="/student/dashboard.php">Dashboard</a>
            <?php endif; ?>

            <!-- ✅ Add this line for Profile -->
            <a href="/profile.php">Profile</a>

            <a href="/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/auth/login.php">Login</a>
            <a href="/auth/register.php">Register</a>
        <?php endif; ?>
    </div>

</header>
<div class="container">
