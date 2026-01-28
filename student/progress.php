<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user_id'];

// Fetch classes the student is enrolled in
$stmt = $pdo->prepare("
    SELECT C.id, C.name 
    FROM CLASSES C 
    JOIN STUDENT_CLASSES SC ON C.id = SC.class_id 
    WHERE SC.student_id = ?
");
$stmt->execute([$studentId]);
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Progress</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; padding: 2rem; }
        h2 { margin-bottom: 1rem; }
        .class-box {
            background: white;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .quiz-block {
            margin-bottom: 1.5rem;
        }
        .progress-bar {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .level {
            flex: 1;
            padding: 1rem;
            border-radius: 8px;
            background: #e5e7eb;
            text-align: center;
            font-weight: bold;
        }
        .level.completed {
            background: #4ade80;
            color: white;
        }
        .score {
            font-size: 0.9rem;
            color: #111827;
        }
        .no-progress { color: #888; font-style: italic; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>📘 My Learning Progress</h2>

<?php if (count($classes) === 0): ?>
    <p class="no-progress">You are not enrolled in any class yet.</p>
<?php else: ?>
    <?php foreach ($classes as $class): ?>
        <div class="class-box">
            <h3><?= htmlspecialchars($class['name']) ?></h3>

            <?php
            // Get all quizzes and student tracking grouped per quiz + difficulty
            $stmt = $pdo->prepare("
                SELECT Q.id AS quiz_id, Q.title, SQT.level_completed, SQT.score
                FROM QUIZZES Q
                LEFT JOIN STUDENT_QUIZ_TRACKING SQT 
                    ON Q.id = SQT.quiz_id AND SQT.student_id = ?
                WHERE Q.class_id = ?
            ");
            $stmt->execute([$studentId, $class['id']]);
            $rawData = $stmt->fetchAll();

            // Group data by quiz
            $quizzes = [];
            foreach ($rawData as $row) {
                $quizId = $row['quiz_id'];
                $difficulty = $row['level_completed'];

                if (!isset($quizzes[$quizId])) {
                    $quizzes[$quizId] = [
                        'title' => $row['title'],
                        'levels' => [1 => null, 2 => null, 3 => null],
                    ];
                }

                if ($difficulty) {
                    $quizzes[$quizId]['levels'][$difficulty] = $row['score'];
                }
            }
            ?>

            <?php if (empty($quizzes)): ?>
                <p class="no-progress">No quizzes available for this class.</p>
            <?php else: ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="quiz-block">
                        <p><strong>📝 <?= htmlspecialchars($quiz['title']) ?></strong></p>
                        <div class="progress-bar">
                            <?php
                            foreach ([1 => 'Easy', 2 => 'Intermediate', 3 => 'Hard'] as $level => $label):
                                $score = $quiz['levels'][$level];
                                ?>
                                <div class="level <?= $score !== null ? 'completed' : '' ?>">
                                    <?= $label ?>
                                    <?php if ($score !== null): ?>
                                        <div class="score"><?= round($score, 2) ?>%</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
</body>
</html>
