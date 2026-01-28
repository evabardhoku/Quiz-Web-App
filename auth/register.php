<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = strtolower(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'student' or 'professor'

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "Email already registered.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO USERS (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);

        echo "Registration successful. You can now <a href='login.php'>login</a>.";
    }
}
?>

<!-- Simple Register Form -->
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
<form method="POST" class ="container">
    <input type="text" name="name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <select name="role" required>
        <option value="">Choose role</option>
        <option value="student">Student</option>
        <option value="professor">Professor</option>
    </select><br>
    <button type="submit">Register</button>
    <hr>
    <a class="back-link" href="login.php">← Login</a>
</form>
