<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student_id = $_SESSION['student_id'];
// Fetch lesson info
$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$lesson) {
    echo '<div class="alert alert-danger">Lesson not found.</div>';
    exit;
}
// Check enrollment
$enroll_stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?');
$enroll_stmt->execute([$student_id, $lesson['course_id']]);
if (!$enroll_stmt->fetch()) {
    echo '<div class="alert alert-warning mt-5">You must enroll in this course to view its lessons.</div>';
    echo '<a href="course_details.php?id=' . $lesson['course_id'] . '" class="btn btn-primary mt-3">Go to Course Page</a>';
    exit;
}
// Mark lesson as in progress
$stmt = $pdo->prepare('INSERT INTO progress (student_id, lesson_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
$stmt->execute([$student_id, $lesson_id, 'In Progress']);

// Mark lesson as completed (add a button or logic for completion)
if (isset($_GET['complete'])) {
    $stmt = $pdo->prepare('UPDATE progress SET status = ? WHERE student_id = ? AND lesson_id = ?');
    $stmt->execute(['Completed', $student_id, $lesson_id]);
    // Redirect to course details page for this lesson
    header('Location: course_details.php?id=' . $lesson['course_id']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($lesson['title']); ?> | Lesson</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br>
    <div class="container mt-5">
        <h2><?php echo htmlspecialchars($lesson['title']); ?></h2>
        <p><?php echo isset($lesson['content']) ? nl2br(htmlspecialchars($lesson['content'])) : '<span class="text-danger">No content available for this lesson.</span>'; ?></p>
        <a href="course_details.php?id=<?php echo $lesson['course_id']; ?>" class="btn btn-secondary">Back to Course</a>
        <?php
        // Check lesson completion status
        $progress_stmt = $pdo->prepare('SELECT status FROM progress WHERE student_id = ? AND lesson_id = ?');
        $progress_stmt->execute([$student_id, $lesson_id]);
        $status = $progress_stmt->fetchColumn();
        if ($status !== 'Completed') {
        ?>
            <a href="lesson.php?id=<?php echo $lesson_id; ?>&complete=1" class="btn btn-success ms-2">Mark as Completed</a>
        <?php } else { ?>
            <a href="quiz.php?lesson_id=<?php echo $lesson_id; ?>" class="btn btn-info ms-2">Take Quiz</a>
        <?php } ?>
    </div>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
