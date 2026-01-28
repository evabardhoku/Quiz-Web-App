<?php
session_start();
require_once '../includes/db.php';
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['quiz_id']) || !isset($_GET['class_id'])) {
    echo "Required parameters not provided.";
    exit;
}

$quizId = $_GET['quiz_id'];
$classId = $_GET['class_id'];
$uploadSuccess = false;
$error = "";

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file_id'])) {
    $deleteId = intval($_POST['delete_file_id']);

    // Fetch file info
    $stmt = $pdo->prepare("SELECT file_path FROM lecture_files WHERE id = ? AND quiz_id = ?");
    $stmt->execute([$deleteId, $quizId]);
    $file = $stmt->fetch();

    if ($file) {
        // Delete file from disk
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }

        // Delete from DB
        $stmt = $pdo->prepare("DELETE FROM lecture_files WHERE id = ?");
        $stmt->execute([$deleteId]);
    }
}


// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lecture_file'])) {
    $file = $_FILES['lecture_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($file['name']);
        $fileTmpPath = $file['tmp_name'];
        $uploadDir = '../uploads/lectures/';
        $targetFilePath = $uploadDir . time() . '_' . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            $stmt = $pdo->prepare("INSERT INTO lecture_files (quiz_id, file_path, file_name, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$quizId, $targetFilePath, $fileName]);
            $uploadSuccess = true;
        } else {
            $error = "Failed to move uploaded file.";
        }
    } else {
        $error = "Upload error code: " . $file['error'];
    }
}

// Fetch uploaded lecture files
//$stmt = $pdo->prepare("SELECT file_name, file_path, uploaded_at FROM lecture_files WHERE quiz_id = ?");
$stmt = $pdo->prepare("SELECT id, file_name, file_path, uploaded_at FROM lecture_files WHERE quiz_id = ?");

$stmt->execute([$quizId]);
$lectureFiles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Lecture</title>
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

        .upload-form, .lecture-list {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .btn {
            text-decoration: none;
            background-color: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-top: 1rem;
            display: inline-block;
        }

        .btn:hover {
            background-color: #059669;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 0.5rem;
        }

        a.file-link {
            text-decoration: none;
            color: #2563eb;
        }

        a.file-link:hover {
            text-decoration: underline;
        }

        label {
            font-weight: bold;
        }

        input[type="file"] {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<h2>📤 Upload Lecture Material</h2>

<div class="upload-form">
    <?php if ($uploadSuccess): ?>
        <p class="success">✅ Lecture uploaded successfully!</p>
    <?php elseif ($error): ?>
        <p class="error">❌ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label>Select lecture file (PDF, video, etc):</label><br>
        <input type="file" name="lecture_file" required><br><br>
        <button class="btn" type="submit">📤 Upload Lecture</button>
    </form>
</div>

<div class="lecture-list">
    <h3>📚 Uploaded Lectures</h3>
    <?php if (count($lectureFiles) > 0): ?>
        <ul>
            <?php foreach ($lectureFiles as $file): ?>
                <li>
                    <a class="file-link" href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank">
                        <?= htmlspecialchars($file['file_name']) ?>
                    </a>
                    <small>— <?= date("F j, Y, g:i a", strtotime($file['uploaded_at'])) ?></small>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this file?');">
                        <input type="hidden" name="delete_file_id" value="<?= $file['id'] ?>">
                        <button class="btn" style="background-color:#ef4444; margin-left:10px;">🗑 Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>

        </ul>
    <?php else: ?>
        <p>No lecture files uploaded yet.</p>
    <?php endif; ?>
</div>

<a class="btn" href="manage_quizzes.php?class_id=<?= htmlspecialchars($classId) ?>">⬅ Back to Quizzes</a>

<?php include '../includes/footer.php'; ?>
</body>
</html>
