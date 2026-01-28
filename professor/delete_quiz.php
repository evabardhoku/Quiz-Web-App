<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$quizId = $_GET['id'] ?? null;
if (!$quizId) {
    echo "Quiz ID missing.";
    exit;
}

// Verify the quiz belongs to this professor
$stmt = $pdo->prepare("SELECT q.id, q.class_id, c.professor_id FROM QUIZZES q JOIN CLASSES c ON q.class_id = c.id WHERE q.id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz || $quiz['professor_id'] != $_SESSION['user_id']) {
    echo "Unauthorized access.";
    exit;
}

$classId = $quiz['class_id'];

// Delete answers
$pdo->prepare("DELETE FROM ANSWERS WHERE question_id IN (SELECT id FROM QUESTIONS WHERE quiz_id = ?)")->execute([$quizId]);

// Delete questions
$pdo->prepare("DELETE FROM QUESTIONS WHERE quiz_id = ?")->execute([$quizId]);

// Delete quiz
$pdo->prepare("DELETE FROM QUIZZES WHERE id = ?")->execute([$quizId]);

header("Location: manage_quizzes.php?class_id=$classId");
exit;
?>
