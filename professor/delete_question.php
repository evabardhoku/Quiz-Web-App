<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$questionId = $_GET['id'] ?? null;
$quizId = $_GET['quiz_id'] ?? null;

if (!$questionId || !$quizId) {
    echo "Invalid request.";
    exit;
}

// Verify professor owns the question
$stmt = $pdo->prepare("SELECT q.*, z.professor_id FROM QUESTIONS q 
    JOIN QUIZZES qu ON q.quiz_id = qu.id 
    JOIN CLASSES z ON qu.class_id = z.id 
    WHERE q.id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch();

if (!$question || $question['professor_id'] != $_SESSION['user_id']) {
    echo "Unauthorized.";
    exit;
}

// Delete answers first
$stmt = $pdo->prepare("DELETE FROM ANSWERS WHERE question_id = ?");
$stmt->execute([$questionId]);

// Then delete question
$stmt = $pdo->prepare("DELETE FROM QUESTIONS WHERE id = ?");
$stmt->execute([$questionId]);

header("Location: edit_questions.php?quiz_id=" . $quizId);
exit;
?>
