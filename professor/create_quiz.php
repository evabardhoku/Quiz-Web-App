<?php
session_start();
require_once '../includes/db.php';
global $pdo;

// Redirect if not logged in or not a professor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$professorId = $_SESSION['user_id'];
$message = "";

// Fetch professor's classes
$stmt = $pdo->prepare("SELECT * FROM CLASSES WHERE professor_id = ?");
$stmt->execute([$professorId]);
$classes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = $_POST['class_id'];
    $quizTitle = trim($_POST['quiz_title']);

    if (!empty($quizTitle) && !empty($classId)) {
        $stmt = $pdo->prepare("INSERT INTO QUIZZES (title, class_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$quizTitle, $classId]);

        $message = "✅ Quiz '$quizTitle' created successfully!";
    } else {
        $message = "❌ Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Quiz</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            padding: 2rem;
        }

        .card {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        input[type="text"], select {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #3b82f6;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #2563eb;
        }

        .message {
            margin-top: 1rem;
            text-align: center;
            font-weight: 600;
        }

        a.back {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #3b82f6;
            text-decoration: none;
        }

        a.back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="card">
    <h2>Create a New Quiz</h2>

    <form method="POST">
        <input type="text" name="quiz_title" placeholder="Quiz title" required>

        <select name="class_id" required>
            <option value="">Select class</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Create Quiz</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
<?php include '../includes/footer.php'; ?>
</html>
