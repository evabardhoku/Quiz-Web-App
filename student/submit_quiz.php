<?php
// submit_quiz.php
global $pdo;
require_once '../includes/db.php'; // PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'], $_POST['student_id'])) {
    $studentId = intval($_POST['student_id']);
    $answers = $_POST['answers']; // [question_id => answer_id]

    $pdo->beginTransaction();
    try {
        // Get correct answers
        $questionIds = array_keys($answers);
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

        $stmt = $pdo->prepare("SELECT id, question_id, is_correct FROM answers WHERE question_id IN ($placeholders)");
        $stmt->execute($questionIds);
        $allAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build a map: [question_id => correct_answer_id]
        $correctMap = [];
        foreach ($allAnswers as $row) {
            if ($row['is_correct']) {
                $correctMap[$row['question_id']] = $row['id'];
            }
        }

        echo "<h2>Quiz Results:</h2>";

        // Insert answers & show results
        $insertStmt = $pdo->prepare("
            INSERT INTO generated_quiz_answers (student_id, question_id, selected_answer_id, is_correct)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($answers as $questionId => $selectedAnswerId) {
            $questionId = intval($questionId);
            $selectedAnswerId = intval($selectedAnswerId);
            $correctAnswerId = $correctMap[$questionId] ?? null;
            $isCorrect = ($selectedAnswerId === $correctAnswerId);

            $insertStmt->execute([$studentId, $questionId, $selectedAnswerId, $isCorrect]);

            echo "<p>Question ID <strong>$questionId</strong>: ";
            echo $isCorrect ? "<span style='color:green;'>✅ Correct</span>" : "<span style='color:red;'>❌ Incorrect</span>";
            echo "</p>";
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid submission.";
}
?>
