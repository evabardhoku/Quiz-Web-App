<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user_id'];
$quizId = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
$level = isset($_GET['level']) ? $_GET['level'] : null;

if (!$quizId || !$level) {
    echo "❌ Quiz or level missing.";
    exit;
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correct = 0;
    $total = count($_POST['answers']);

    foreach ($_POST['answers'] as $questionId => $selectedAnswerId) {
        $stmt = $pdo->prepare("SELECT is_correct FROM ANSWERS WHERE id = ? AND question_id = ?");
        $stmt->execute([$selectedAnswerId, $questionId]);
        if ($stmt->fetchColumn()) {
            $correct++;
        }
    }

    $score = ($correct / $total) * 100;

    // Save result
    $stmt = $pdo->prepare("INSERT INTO STUDENT_QUIZ_TRACKING (student_id, quiz_id, level_completed, score, completed_at)
                           VALUES (?, ?, ?, ?, NOW())
                           ON DUPLICATE KEY UPDATE score = VALUES(score), completed_at = NOW()");
    $stmt->execute([$studentId, $quizId, $level, $score]);

    header("Location: view_class_quizzes.php?class_id=" . $_POST['class_id']);
    exit;
}

// Fetch quiz info
$stmt = $pdo->prepare("SELECT * FROM QUIZZES WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo "❌ Quiz not found.";
    exit;
}

// Fetch questions for this level
$stmt = $pdo->prepare("SELECT * FROM QUESTIONS WHERE quiz_id = ? AND level = ?");
$stmt->execute([$quizId, $level]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Quiz</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9fafb;
            padding: 2rem;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
        }

        form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .question {
            margin-bottom: 2rem;
        }

        .question h4 {
            margin-bottom: 0.8rem;
        }

        .answer {
            margin: 0.5rem 0;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: bold;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .submit-btn:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>Quiz: <?= htmlspecialchars($quiz['title']) ?> — <?= ['','Easy','Intermediate','Hard'][$level] ?> Level</h2>

<form method="POST">
    <input type="hidden" name="class_id" value="<?= htmlspecialchars($quiz['class_id']) ?>">

    <?php foreach ($questions as $q): ?>
        <div class="question">
            <h4><?= htmlspecialchars($q['question_text']) ?></h4>

            <?php
            $stmt = $pdo->prepare("SELECT * FROM ANSWERS WHERE question_id = ?");
            $stmt->execute([$q['id']]);
            $answers = $stmt->fetchAll();
            ?>

            <?php foreach ($answers as $a): ?>
                <div class="answer">
                    <label>
                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $a['id'] ?>" required>
                        <?= htmlspecialchars($a['answer_text']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <button class="submit-btn" type="submit">Submit Quiz</button>
</form>

</body>
<?php include '../includes/footer.php'; ?>
</html>
