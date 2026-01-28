<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$professorId = $_SESSION['user_id'];

if (!isset($_GET['class_id'])) {
    echo "Class ID not provided.";
    exit;
}

$classId = $_GET['class_id'];

// Verify professor owns the class
$stmt = $pdo->prepare("SELECT * FROM CLASSES WHERE id = ? AND professor_id = ?");
$stmt->execute([$classId, $professorId]);
$class = $stmt->fetch();

if (!$class) {
    echo "You are not authorized to view this class.";
    exit;
}

// Get all quizzes for this class
$stmt = $pdo->prepare("SELECT * FROM QUIZZES WHERE class_id = ? ORDER BY created_at DESC");
$stmt->execute([$classId]);
$quizzes = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<head>
    <link rel="stylesheet" href="../styles.css">
</head>
<h2>📚 Quizzes for <?= htmlspecialchars($class['name']) ?></h2>

<a class="btn" href="create_quiz.php?class_id=<?= $classId ?>">➕ Create New Quiz</a>
<br><br>

<?php if (count($quizzes) === 0): ?>
    <p>No quizzes created yet for this class.</p>
<?php else: ?>
    <?php foreach ($quizzes as $quiz): ?>
        <div class="card">
            <div class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></div>
            <div style="margin-top: 0.5rem;">
                <a class="btn" href="edit_quiz.php?id=<?= $quiz['id'] ?>">✏️ Edit</a>
                <a class="btn" href="edit_questions.php?quiz_id=<?= $quiz['id'] ?>">📝 Modify Questions</a>
                <a class="btn delete-btn" href="delete_quiz.php?id=<?= $quiz['id'] ?>" onclick="return confirm('Are you sure you want to delete this quiz?')">🗑️ Delete</a>
                <a class="btn" href="upload_lecture.php?quiz_id=<?= $quiz['id'] ?>&class_id=<?= $classId ?>">➕ Add Lecture</a>

            </div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
