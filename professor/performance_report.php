<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

$professorId = $_SESSION['user_id'];

// Fetch classes taught by this professor
$stmt = $pdo->prepare("SELECT * FROM CLASSES WHERE professor_id = ?");
$stmt->execute([$professorId]);
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Performance Report</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; padding: 2rem; }
        h2 { margin-bottom: 1rem; }
        .class-box { background: white; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f9fafb; }
        .no-data { color: #888; font-style: italic; }
        .btn { text-decoration: none; padding: 0.5rem 1rem; background: #007bff; color: white; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Student Performance Report</h2>

<?php foreach ($classes as $class): ?>
    <div class="class-box">
        <h3><?= htmlspecialchars($class['name']) ?></h3>

        <?php
        // Fetch students enrolled in this class
        $stmt = $pdo->prepare("
            SELECT U.id, U.name, U.email
            FROM USERS U
            JOIN STUDENT_CLASSES SC ON U.id = SC.student_id
            WHERE SC.class_id = ?
        ");
        $stmt->execute([$class['id']]);
        $students = $stmt->fetchAll();
        ?>

        <?php if (count($students) === 0): ?>
            <p class="no-data">No students enrolled yet.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Avg. Easy Score</th>
                    <th>Avg. Intermediate Score</th>
                    <th>Avg. Hard Score</th>
                    <th>Quizzes Completed</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($students as $student): ?>
                    <?php
                    // Fetch avg score for each difficulty level (easy, intermediate, hard)
                    $stmt = $pdo->prepare("
                        SELECT 
                            SUM(CASE WHEN level_completed = 1 THEN score ELSE 0 END) / COUNT(CASE WHEN level_completed = 1 THEN 1 END) AS easy_score,
                            SUM(CASE WHEN level_completed = 2 THEN score ELSE 0 END) / COUNT(CASE WHEN level_completed = 2 THEN 1 END) AS intermediate_score,
                            SUM(CASE WHEN level_completed = 3 THEN score ELSE 0 END) / COUNT(CASE WHEN level_completed = 3 THEN 1 END) AS hard_score,
                            COUNT(*) AS completed
                        FROM STUDENT_QUIZ_TRACKING
                        WHERE student_id = ? AND quiz_id IN (
                            SELECT id FROM QUIZZES WHERE class_id = ?
                        )
                    ");
                    $stmt->execute([$student['id'], $class['id']]);
                    $result = $stmt->fetch();
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= $result['easy_score'] ? round($result['easy_score'], 2) : '-' ?></td>
                        <td><?= $result['intermediate_score'] ? round($result['intermediate_score'], 2) : '-' ?></td>
                        <td><?= $result['hard_score'] ? round($result['hard_score'], 2) : '-' ?></td>
                        <td><?= $result['completed'] ?></td>
                        <td><a class="btn" href="view_full_attempt.php?student_id=<?= $student['id'] ?>&class_id=<?= $class['id'] ?>">🔍 View Full Attempt</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</body>
<?php include '../includes/footer.php'; ?>
</html>
