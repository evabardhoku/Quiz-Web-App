<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in or not a professor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

// Check for class ID
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$classId = $_GET['id'];

// Check class ownership
$stmt = $pdo->prepare("SELECT * FROM CLASSES WHERE id = ? AND professor_id = ?");
$stmt->execute([$classId, $_SESSION['user_id']]);
$class = $stmt->fetch();

if (!$class) {
    echo "❌ Class not found or access denied.";
    exit;
}

// Get quizzes for this class
$stmt = $pdo->prepare("SELECT * FROM QUIZZES WHERE class_id = ? ORDER BY created_at DESC");
$stmt->execute([$classId]);
$quizzes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($class['name']) ?> - Class View</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            padding: 2rem;
        }

        .card {
            max-width: 600px;
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

        .quiz {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .quiz:last-child {
            border-bottom: none;
        }

        .quiz-title {
            font-weight: 600;
        }

        .btn {
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: inline-block;
        }

        .btn:hover {
            background-color: #2563eb;
        }

        a.back {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: #3b82f6;
            text-decoration: none;
        }

        a.back:hover {
            text-decoration: underline;
        }

        .empty {
            text-align: center;
            color: #888;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="card">
    <h2><?= htmlspecialchars($class['name']) ?> - Quizzes</h2>

    <?php if (count($quizzes) > 0): ?>
        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz">
                <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>
                <a class="btn" href="add_questions.php?quiz_id=<?= $quiz['id'] ?>">+ Add Questions</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">No quizzes created yet for this class.</div>
    <?php endif; ?>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
<?php include '../includes/footer.php'; ?>
</html>
