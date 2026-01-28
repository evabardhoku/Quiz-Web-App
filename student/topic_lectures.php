<?php
global $pdo;
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['class_id'])) {
    echo "Class ID not provided.";
    exit;
}

$classId = $_GET['class_id'];

// Verify student is enrolled in the class
$stmt = $pdo->prepare("
    SELECT C.name, U.name AS professor_name
    FROM STUDENT_CLASSES SC
    JOIN CLASSES C ON SC.class_id = C.id
    JOIN USERS U ON C.professor_id = U.id
    WHERE SC.class_id = ? AND SC.student_id = ?
");
$stmt->execute([$classId, $_SESSION['user_id']]);
$classInfo = $stmt->fetch();

if (!$classInfo) {
    echo "You are not authorized to view this class.";
    exit;
}

// Fetch lecture files linked to quizzes in this class
$stmt = $pdo->prepare("
    SELECT LF.file_name, LF.file_path, LF.uploaded_at, Q.title AS quiz_title
    FROM lecture_files LF
    JOIN quizzes Q ON LF.quiz_id = Q.id
    WHERE Q.class_id = ?
    ORDER BY LF.uploaded_at DESC
");
$stmt->execute([$classId]);
$lectures = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecture Materials</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 2rem;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
        }

        .lecture-card {
            background: white;
            padding: 1.5rem;
            margin-top: 3%;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .lecture-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .lecture-info {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .lecture-date {
            font-size: 0.85rem;
            color: #777;
        }

        a.btn {
            text-decoration: none;
            background-color: #3b82f6;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        a.btn:hover {
            background-color: #2563eb;
        }

        .empty {
            text-align: center;
            color: #888;
            font-style: italic;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>🎓 Lecture Materials for <?= htmlspecialchars($classInfo['name']) ?> <br><span style="font-size: 0.9rem;">(Professor: <?= htmlspecialchars($classInfo['professor_name']) ?>)</span></h2>

<?php if (count($lectures) === 0): ?>
    <p class="empty">No lecture materials have been uploaded for this class yet.</p>
<?php else: ?>
    <?php foreach ($lectures as $lecture): ?>
        <div class="lecture-card">
            <div class="lecture-title"><?= htmlspecialchars($lecture['file_name']) ?></div>
            <div class="lecture-info">Related to: <?= htmlspecialchars($lecture['quiz_title']) ?></div>
            <div class="lecture-date">Uploaded on: <?= date('F j, Y, H:i', strtotime($lecture['uploaded_at'])) ?></div>
            <br>
            <a class="btn" href="<?= htmlspecialchars($lecture['file_path']) ?>" target="_blank">📥 Download / View</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
<?php include '../includes/footer.php'; ?>
</html>
