<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in or not a professor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$professorId = $_SESSION['user_id'];

// Fetch professor's classes
$stmt = $pdo->prepare("SELECT * FROM CLASSES WHERE professor_id = ?");
$stmt->execute([$professorId]);
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Professor Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }

        header {
            background-color: #1e293b;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            margin: 0;
        }

        .container {
            padding: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .class-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn:hover {
            background-color: #2563eb;
        }

        .delete-btn {
            background-color: #ef4444;
        }

        .delete-btn:hover {
            background-color: #dc2626;
        }

        .class-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .logout {
            color: #fca5a5;
            text-decoration: none;
        }

        .logout:hover {
            color: #f87171;
        }

        .top-actions {
            margin: 1rem 0;
        }
    </style>
</head>
<body>

<header>
    <?php include '../includes/header.php'; ?>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h1>
</header>

<div class="container">

    <div class="top-actions">
        <a class="btn" href="create_class.php">➕ Create New Class</a>
        <a class="btn" href="create_quiz.php">📚 Create New Quiz</a>
        <a class="btn" href="performance_report.php">📊 View Performance Report</a>
    </div>

    <h2>Your Classes</h2>

    <?php if (count($classes) > 0): ?>
    <?php foreach ($classes as $class): ?>
    <div class="card">
        <div class="class-title"><?= htmlspecialchars($class['name']) ?></div>

        <a class="btn" href="view_class.php?id=<?= $class['id'] ?>">Add Questions</a>
        <a class="btn" href="manage_quizzes.php?class_id=<?= $class['id'] ?>">📚 View Topics</a>
        <a class="btn delete-btn" href="delete_class.php?id=<?= $class['id'] ?>" onclick="return confirm('Are you sure you want to delete this class and all related quizzes?')">🗑️ Delete Class</a>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
        <p>You haven't created any classes yet.</p>
    <?php endif; ?>

</div>

</body>
<?php include '../includes/footer.php'; ?>
</html>
