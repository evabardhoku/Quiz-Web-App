<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in or not a professor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['quiz_id'])) {
    echo "❌ Quiz ID is missing.";
    exit;
}

$quizId = $_GET['quiz_id'];
$professorId = $_SESSION['user_id'];
$message = "";

// Check quiz ownership
$stmt = $pdo->prepare("SELECT Q.*, C.professor_id FROM QUIZZES Q JOIN CLASSES C ON Q.class_id = C.id WHERE Q.id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz || $quiz['professor_id'] != $professorId) {
    echo "❌ You do not have permission to add questions to this quiz.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionText = trim($_POST['question_text']);
    $level = (int)$_POST['level'];
    $answers = $_POST['answers'];
    $correctIndex = (int)$_POST['correct_index'];

    if (!empty($questionText) && count($answers) === 4 && isset($correctIndex)) {
        // Insert the question
        $stmt = $pdo->prepare("INSERT INTO QUESTIONS (quiz_id, question_text, level) VALUES (?, ?, ?)");
        $stmt->execute([$quizId, $questionText, $level]);
        $questionId = $pdo->lastInsertId();

        // Insert answers
        foreach ($answers as $index => $text) {
            $isCorrect = ($index == $correctIndex) ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO ANSWERS (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$questionId, trim($text), $isCorrect]);
        }

        $message = "✅ Question added successfully!";
    } else {
        $message = "❌ Please fill all fields and mark one correct answer.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            padding: 2rem;
        }

        .card {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        textarea, input[type="text"], select {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .answers label {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
        }

        .answers input[type="radio"] {
            margin-right: 0.5rem;
        }

        button {
            background-color: #3b82f6;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #2563eb;
        }

        .message {
            margin-top: 1rem;
            text-align: center;
            font-weight: 600;
        }

        a.back {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: #3b82f6;
            text-decoration: none;
        }

        a.back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="card">
    <h2>Add Question to: <?= htmlspecialchars($quiz['title']) ?></h2>

    <form method="POST">
        <label for="question_text">Question:</label>
        <textarea name="question_text" rows="3" required></textarea>

        <label for="level">Difficulty Level:</label>
        <select name="level" required>
            <option value="">Select level</option>
            <option value="1">Easy</option>
            <option value="2">Intermediate</option>
            <option value="3">Hard</option>
        </select>

        <div class="answers">
            <label><input type="radio" name="correct_index" value="0" required> <input type="text" name="answers[]" placeholder="Answer 1" required></label>
            <label><input type="radio" name="correct_index" value="1"> <input type="text" name="answers[]" placeholder="Answer 2" required></label>
            <label><input type="radio" name="correct_index" value="2"> <input type="text" name="answers[]" placeholder="Answer 3" required></label>
            <label><input type="radio" name="correct_index" value="3"> <input type="text" name="answers[]" placeholder="Answer 4" required></label>
        </div>

        <button type="submit">Add Question</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <a class="back" href="view_class.php?id=<?= $quiz['class_id'] ?>">← Back to Class</a>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
