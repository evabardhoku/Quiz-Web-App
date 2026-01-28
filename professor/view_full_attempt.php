<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$professorId = $_SESSION['user_id'];

if (!isset($_GET['student_id']) || !isset($_GET['class_id'])) {
    echo "Student ID or Class ID not provided.";
    exit;
}

$studentId = $_GET['student_id'];
$classId = $_GET['class_id'];

// Verify that the professor teaches this class
$stmt = $pdo->prepare("SELECT * FROM CLASSES WHERE id = ? AND professor_id = ?");
$stmt->execute([$classId, $professorId]);
$class = $stmt->fetch();

if (!$class) {
    echo "You are not authorized to view this class.";
    exit;
}

// Fetch all quizzes for this class
$stmt = $pdo->prepare("SELECT * FROM QUIZZES WHERE class_id = ?");
$stmt->execute([$classId]);
$quizzes = $stmt->fetchAll();

// Fetch the quiz attempt details
$attempts = [];
foreach ($quizzes as $quiz) {
    $stmt = $pdo->prepare("
        SELECT SQ.score, SQ.level_completed, Q.question_text, A.answer_text, A.is_correct
        FROM STUDENT_QUIZ_TRACKING SQ
        JOIN QUESTIONS Q ON Q.quiz_id = SQ.quiz_id
        JOIN ANSWERS A ON A.question_id = Q.id
        WHERE SQ.student_id = ? AND SQ.quiz_id = ?
    ");
    $stmt->execute([$studentId, $quiz['id']]);
    $attempts[$quiz['id']] = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Full Attempt</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; padding: 2rem; }
        h2 { margin-bottom: 1rem; }
        .class-box { background: white; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f9fafb; }
        .correct { background-color: #d4edda; }
        .incorrect { background-color: #f8d7da; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>Full Attempt for Student: <?= htmlspecialchars($studentId) ?> in <?= htmlspecialchars($class['name']) ?></h2>

<?php foreach ($quizzes as $quiz): ?>
    <div class="class-box">
        <h3>Quiz: <?= htmlspecialchars($quiz['title']) ?></h3>

        <?php if (isset($attempts[$quiz['id']])): ?>
            <table>
                <tr>
                    <th>Question</th>
                    <th>Answer</th>
                    <th>Correct?</th>
                </tr>

                <?php foreach ($attempts[$quiz['id']] as $attempt): ?>
                    <tr class="<?= $attempt['is_correct'] ? 'correct' : 'incorrect' ?>">
                        <td><?= htmlspecialchars($attempt['question_text']) ?></td>
                        <td><?= htmlspecialchars($attempt['answer_text']) ?></td>
                        <td><?= $attempt['is_correct'] ? '✔️ Correct' : '❌ Incorrect' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No attempt found for this quiz.</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</body>
<?php include '../includes/footer.php'; ?>
</html>
