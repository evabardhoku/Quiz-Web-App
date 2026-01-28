<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'professor') {
            header("Location: ../professor/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit;
    } else {
        echo "Invalid credentials.";
    }
}
?>

<!-- Simple Login Form -->
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
    }
    .container {
        max-width: 400px;
        margin: 100px auto;
        padding: 20px;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    input[type="text"], input[type="email"], input[type="password"], select {
        width: 95%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    button {
        width: 100%;
        padding: 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    button:hover {
        background-color: #45a049;
    }
    .back-link {
        text-align: center;
        margin-top: 2rem;
        display: block;
        color: #45a049;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }

</style>
<form method="POST" class="container">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
    <hr>
    <a class="back-link" href="register.php">← Register</a>
</form>
