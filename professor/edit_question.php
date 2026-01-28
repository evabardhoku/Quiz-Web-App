<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$questionId = $_GET['id'] ?? null;
if (!$questionId) {
    echo "Question ID missing.";
    exit;
}

// Fetch question and validate ownership
$stmt = $pdo->prepare("SELECT q.*, z.professor_id FROM QUESTIONS q 
    JOIN QUIZZES qu ON q.quiz_id = qu.id 
    JOIN CLASSES z ON qu.class_id = z.id 
    WHERE q.id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch();

if (!$question || $question['professor_id'] != $_SESSION['user_id']) {
    echo "Unauthorized access.";
    exit;
}

// Fetch answers
$stmt = $pdo->prepare("SELECT * FROM ANSWERS WHERE question_id = ?");
$stmt->execute([$questionId]);
$answers = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $level = $_POST['level'];
    $correct_answer_index = $_POST['correct'];

    // Update question
    $stmt = $pdo->prepare("UPDATE QUESTIONS SET question_text = ?, level = ? WHERE id = ?");
    $stmt->execute([$question_text, $level, $questionId]);

    // Update answers
    for ($i = 0; $i < 4; $i++) {
        $answer_text = $_POST["answer_$i"];
        $is_correct = ($i == $correct_answer_index) ? 1 : 0;
        $answer_id = $answers[$i]['id'];
        $stmt = $pdo->prepare("UPDATE ANSWERS SET answer_text = ?, is_correct = ? WHERE id = ?");
        $stmt->execute([$answer_text, $is_correct, $answer_id]);
    }

    header("Location: edit_questions.php?quiz_id=" . $question['quiz_id']);
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<head>
    <style>
        /* General page layout */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f6f8fa;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        /* Form styling */
        form {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
            max-width: 700px;
            margin: auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            color: #444;
        }

        /* Textarea styling */
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            resize: vertical;
        }

        /* Dropdown */
        select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            width: 100%;
        }

        /* Radio buttons and answer inputs */
        input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.2);
            vertical-align: middle;
        }

        input[type="text"] {
            padding: 8px;
            margin-bottom: 10px;
            width: 85%;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        /* Submit button */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 15px;
            background-color: #2d89ef;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #1b61c1;
        }

    </style>
</head>
<h2>✏️ Edit Question</h2>

<form method="POST">
    <label>Question Text:</label><br>
    <textarea name="question_text" rows="3" cols="60" required><?= htmlspecialchars($question['question_text']) ?></textarea><br><br>

    <label>Difficulty Level:</label><br>
    <select name="level" required>
        <option value="1" <?= $question['level'] == 1 ? 'selected' : '' ?>>Easy</option>
        <option value="2" <?= $question['level'] == 2 ? 'selected' : '' ?>>Intermediate</option>
        <option value="3" <?= $question['level'] == 3 ? 'selected' : '' ?>>Hard</option>
    </select><br><br>

    <label>Answers:</label><br>
    <?php foreach ($answers as $index => $ans): ?>
        <input type="radio" name="correct" value="<?= $index ?>" <?= $ans['is_correct'] ? 'checked' : '' ?>>
        <input type="text" name="answer_<?= $index ?>" value="<?= htmlspecialchars($ans['answer_text']) ?>" required><br>
    <?php endforeach; ?>

    <br>
    <button class="btn" type="submit">💾 Save Changes</button>
</form>

<?php include '../includes/footer.php'; ?>
