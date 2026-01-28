<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user_id'];

// Enroll action
if (isset($_GET['enroll'])) {
    $classId = $_GET['enroll'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO STUDENT_CLASSES (student_id, class_id) VALUES (?, ?)");
    $stmt->execute([$studentId, $classId]);
    header("Location: enroll_class.php");
    exit;
}

// Fetch all classes
$stmt = $pdo->query("SELECT C.*, U.name AS professor_name
                     FROM CLASSES C
                     JOIN USERS U ON C.professor_id = U.id");
$allClasses = $stmt->fetchAll();

// Get enrolled class IDs
$stmt = $pdo->prepare("SELECT class_id FROM STUDENT_CLASSES WHERE student_id = ?");
$stmt->execute([$studentId]);
$enrolledIds = array_column($stmt->fetchAll(), 'class_id');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Enroll in Class</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8fafc;
            padding: 2rem;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
        }

        .class-box {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.2rem;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .class-info {
            flex-grow: 1;
        }

        .class-info h4 {
            margin: 0;
        }

        .class-info p {
            color: #555;
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }

        .enroll-btn {
            padding: 0.5rem 1rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .enroll-btn:hover {
            background-color: #2563eb;
        }

        .enrolled-label {
            padding: 0.5rem 1rem;
            background-color: #10b981;
            color: white;
            border-radius: 8px;
            font-weight: bold;
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
            display: block;
            color: #3b82f6;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Available Classes</h2>

<?php foreach ($allClasses as $class): ?>
    <div class="class-box">
        <div class="class-info">
            <h4><?= htmlspecialchars($class['name']) ?></h4>
            <p>Professor: <?= htmlspecialchars($class['professor_name']) ?></p>
        </div>

        <?php if (in_array($class['id'], $enrolledIds)): ?>
            <div class="enrolled-label">Enrolled ✅</div>
        <?php else: ?>
            <a class="enroll-btn" href="enroll_class.php?enroll=<?= $class['id'] ?>">Enroll</a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<a class="back-link" href="dashboard.php">← Back to Dashboard</a>

</body>
<?php include '../includes/footer.php'; ?>
</html>
