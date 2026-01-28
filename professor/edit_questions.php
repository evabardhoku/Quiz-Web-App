<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$quizId = $_GET['quiz_id'] ?? null;
if (!$quizId) {
    echo "Quiz ID is missing.";
    exit;
}

// Verify quiz belongs to professor
$stmt = $pdo->prepare("SELECT q.*, c.professor_id FROM QUIZZES q JOIN CLASSES c ON q.class_id = c.id WHERE q.id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz || $quiz['professor_id'] != $_SESSION['user_id']) {
    echo "Unauthorized access.";
    exit;
}

// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM QUESTIONS WHERE quiz_id = ?");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<head>
    <link rel="stylesheet" href="../styles.css">
</head>
<h2>📝 Manage Questions for: <em><?= htmlspecialchars($quiz['title']) ?></em></h2>

<a class="btn" href="add_questions.php?quiz_id=<?= $quizId ?>">➕ Add New Question</a>
<br><br>

<?php if (count($questions) === 0): ?>
    <p>No questions added yet.</p>
<?php else: ?>
    <?php foreach ($questions as $q): ?>
        <div class="card">
            <div><strong>Question:</strong> <?= htmlspecialchars($q['question_text']) ?></div>
            <div><strong>Level:</strong>
                <?php
                echo $q['level'] == 1 ? "Easy" : ($q['level'] == 2 ? "Intermediate" : "Hard");
                ?>
            </div>
            <div style="margin-top: 0.5rem;">
                <a class="btn" href="edit_question.php?id=<?= $q['id'] ?>">✏️ Edit</a>
                <a class="btn delete-btn" href="delete_question.php?id=<?= $q['id'] ?>&quiz_id=<?= $quizId ?>" onclick="return confirm('Are you sure you want to delete this question?')">🗑️ Delete</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
