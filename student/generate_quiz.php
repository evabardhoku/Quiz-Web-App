<?php
global $pdo;
require_once '../includes/db.php'; // PDO connection
require_once 'QuizGenerator.php';
include '../includes/header.php';

// Get the student ID from query string or session (for now using GET)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user_id'];
$questionCount = 10;

// Use QuizGenerator to fetch personalized quiz
$quizGen = new QuizGenerator($pdo, $studentId, true);
$questions = $quizGen->generateQuiz($questionCount);
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

<h2>Take Your Personalized Quiz</h2>

<?php if (empty($questions)): ?>
    <p style="text-align: center;">No questions available at the moment. Please check back later.</p>
<?php else: ?>
    <form method="POST" action="submit_quiz.php">
        <?php foreach ($questions as $q): ?>
            <div class="question">
                <h4><?= htmlspecialchars($q['question_text']) ?></h4>

                <?php foreach ($q['answers'] as $a): ?>
                    <div class="answer">
                        <label>
                            <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $a['id'] ?>" required>
                            <?= htmlspecialchars($a['answer_text']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId) ?>">
        <button class="submit-btn" type="submit">Submit Quiz</button>
    </form>
<?php endif; ?>

</body>
<?php include '../includes/footer.php'; ?>
</html>
