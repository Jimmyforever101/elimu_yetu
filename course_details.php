<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
$student_id = $_SESSION['student_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Handle enrollment
$enrolled = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?, ?)');
    $stmt->execute([$student_id, $course_id]);
    $enrolled = true;
}
// Check enrollment status
$stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?');
$stmt->execute([$student_id, $course_id]);
if ($stmt->fetch()) {
    $enrolled = true;
}
// Fetch course info
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<div class="alert alert-danger">Course not found.</div>';
    exit;
}
// Fetch lessons
$stmt = $pdo->prepare('SELECT * FROM lessons WHERE course_id = ? ORDER BY id');
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch progress
$progress_stmt = $pdo->prepare('SELECT lesson_id, status FROM progress WHERE student_id = ?');
$progress_stmt->execute([$student_id]);
$progress = $progress_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($course['title']); ?> | Course Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br>
    <div class="container pt-5" style="padding-top:100px;">
        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
        <p><?php echo htmlspecialchars($course['description']); ?></p>
        <?php if (!$enrolled): ?>
            <form method="post" action="">
                <button type="submit" name="enroll" class="btn btn-success mb-3">Enroll in this Course</button>
            </form>
        <?php else: ?>
            <div class="alert alert-success">You are enrolled in this course.</div>
        <?php endif; ?>
        <h4>Lessons</h4>
        <?php if (count($lessons) === 0): ?>
            <div class="alert alert-info">No lessons found for this course.</div>
        <?php else: ?>
            <div class="table-responsive">
            <ul class="list-group mb-4">
                <?php foreach ($lessons as $lesson):
                    $status = $progress[$lesson['id']] ?? 'Not Started';
                ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <?php echo htmlspecialchars($lesson['title']); ?>
                            <?php if ($status === 'Completed'): ?>
                                <span class="badge bg-success ms-2">Completed</span>
                            <?php elseif ($status === 'In Progress'): ?>
                                <span class="badge bg-warning ms-2">In Progress</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2">Not Started</span>
                            <?php endif; ?>
                        </span>
                        <span>
                            <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-primary">View Lesson</a>
                            <?php if ($status === 'Completed'): ?>
                                <a href="quiz.php?lesson_id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-info ms-2">Take Quiz</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-info ms-2" disabled title="Complete the lesson to unlock the quiz">Take Quiz</button>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
