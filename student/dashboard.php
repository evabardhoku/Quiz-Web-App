<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user_id'];

// Get classes the student is enrolled in
$stmt = $pdo->prepare("
    SELECT C.id, C.name, U.name AS professor_name
    FROM STUDENT_CLASSES SC
    JOIN CLASSES C ON SC.class_id = C.id
    JOIN USERS U ON C.professor_id = U.id
    WHERE SC.student_id = ?
");
$stmt->execute([$studentId]);
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 2rem;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
        }

        .class-card {
            background: white;
            padding: 1.5rem;
            margin-top: 3%;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .class-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .class-info {
            color: #555;
            margin-bottom: 1rem;
        }

        a.btn {
            text-decoration: none;
            background-color: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        a.btn:hover {
            background-color: #059669;
        }

        .empty {
            text-align: center;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
<a class="btn" href="enroll_class.php" >➕ Browse Classes to Enroll</a>
<a class="btn" href="progress.php" >📊 My Progress</a>


<?php if (count($classes) > 0): ?>
    <?php foreach ($classes as $class): ?>
        <div class="class-card">
            <div class="class-title"><?= htmlspecialchars($class['name']) ?></div>
            <div class="class-info">Professor: <?= htmlspecialchars($class['professor_name']) ?></div>
            <a class="btn" href="view_class_quizzes.php?class_id=<?= $class['id'] ?>">📘 View Quizzes</a>
            <a class="btn" href="topic_lectures.php?class_id=<?= $class['id'] ?>">🎥 View Lecture</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>You are not enrolled in any class.</p>
    <a href="enroll_class.php" class="enroll-btn">Browse Classes to Enroll</a>
<?php endif; ?>

</body>
<?php include '../includes/footer.php'; ?>
</html>
