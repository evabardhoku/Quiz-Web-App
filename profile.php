<?php
session_start();
require_once 'includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch user details
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$name || !$email) {
        $error = "Name and email are required.";
    } else {
        try {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $hashedPassword, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $userId]);
            }
            $success = "✅ Profile updated successfully.";
            // Refresh user data
            $user['name'] = $name;
            $user['email'] = $email;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email already in use.";
            } else {
                $error = "Update failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        h2 {
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.6rem;
            margin: 0.5rem 0 1rem 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn {
            background-color: #2563eb;
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .success {
            color: green;
            margin-bottom: 1rem;
        }

        .error {
            color: red;
            margin-bottom: 1rem;
        }

        a.back {
            display: inline-block;
            margin-top: 1.5rem;
            text-decoration: none;
            color: #2563eb;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>👤 Update Profile</h2>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>New Password (leave blank to keep current):</label>
        <input type="password" name="password">

        <button class="btn" type="submit">💾 Save Changes</button>
    </form>

    <a class="back" href="../dashboard.php">⬅ Back to Dashboard</a>
</div>

</body>
</html>
