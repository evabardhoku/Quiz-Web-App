<?php
global $pdo;
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user_id'];

if (!isset($_GET['class_id'])) {
    echo "❌ Class ID is missing.";
    exit;
}

$classId = $_GET['class_id'];

// Fetch class info
$stmt = $pdo->prepare("SELECT C.*, U.name AS professor_name FROM CLASSES C JOIN USERS U ON C.professor_id = U.id WHERE C.id = ?");
$stmt->execute([$classId]);
$class = $stmt->fetch();

if (!$class) {
    echo "❌ Class not found.";
    exit;
}

// Get all quizzes for this class
$stmt = $pdo->prepare("SELECT * FROM QUIZZES WHERE class_id = ? ORDER BY created_at DESC");
$stmt->execute([$classId]);
$quizzes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Quizzes</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9fafb;
            padding: 2rem;
        }

        h2 {
            text-align: center;
            margin-bottom: 0.5rem;
        }

        h4 {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
        }

        .quiz-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .quiz-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .levels {
            display: flex;
            gap: 1rem;
        }

        .level-box {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background: #f3f4f6;
            transition: all 0.2s ease;
        }

        .level-box.completed {
            background-color: #10b981;
            color: white;
        }

        .level-box .label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .level-box a {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.4rem 0.8rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .level-box a:hover {
            background-color: #2563eb;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 2rem;
            text-decoration: none;
            color: #3b82f6;
        }

        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>Class: <?= htmlspecialchars($class['name']) ?></h2>
<h4>Professor: <?= htmlspecialchars($class['professor_name']) ?></h4>

<div style="text-align: center; margin-bottom: 2rem;">

    <a href="generate_quiz.php?quiz_id=1" class="btn btn-primary">Generate Quiz</a>

</div>


<?php if (count($quizzes) > 0): ?>
    <?php foreach ($quizzes as $quiz): ?>
        <div class="quiz-card">
            <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>

            <div class="levels">
                <?php for ($level = 1; $level <= 3; $level++): ?>
                    <?php
                    $levelNames = [1 => "Easy", 2 => "Intermediate", 3 => "Hard"];
                    $stmt = $pdo->prepare("SELECT * FROM STUDENT_QUIZ_TRACKING WHERE student_id = ? AND quiz_id = ? AND level_completed = ?");
                    $stmt->execute([$studentId, $quiz['id'], $level]);
                    $completed = $stmt->fetch();
                    ?>
                    <div class="level-box <?= $completed ? 'completed' : '' ?>">
                        <div class="label"><?= $levelNames[$level] ?></div>
                        <?php if ($completed): ?>
                            ✅ Completed<br>Score: <?= $completed['score'] ?>
                        <?php else: ?>
                            <a href="take_quiz.php?quiz_id=<?= $quiz['id'] ?>&level=<?= $level ?>">Start</a>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align: center; color: #777;">No quizzes yet for this class.</p>
<?php endif; ?>

<a class="back" href="dashboard.php">← Back to Dashboard</a>

</body>
<?php include '../includes/footer.php'; ?>
</html>
