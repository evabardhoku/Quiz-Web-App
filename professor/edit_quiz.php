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

// Fetch quiz
$stmt = $pdo->prepare("SELECT q.*, c.professor_id FROM QUIZZES q JOIN CLASSES c ON q.class_id = c.id WHERE q.id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz || $quiz['professor_id'] != $_SESSION['user_id']) {
    echo "Unauthorized access.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);

    if ($title !== '') {
        $stmt = $pdo->prepare("UPDATE QUIZZES SET title = ? WHERE id = ?");
        $stmt->execute([$title, $quizId]);
        header("Location: manage_quizzes.php?class_id=" . $quiz['class_id']);
        exit;
    } else {
        $error = "Title cannot be empty.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>✏️ Edit Quiz</h2>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label for="title">Quiz Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" required style="padding:0.5rem; width:300px;"><br><br>
    <button type="submit" class="btn">💾 Save Changes</button>
</form>

<?php include '../includes/footer.php'; ?>
