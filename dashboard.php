<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
$student_id = $_SESSION['student_id'];
// Fetch enrolled courses
$stmt = $pdo->prepare('SELECT c.* FROM courses c INNER JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ?');
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Elimu Yetu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br>
    <div class="container mt-5">
        <h2>My Enrolled Courses</h2>
        <?php if (count($courses) === 0): ?>
            <div class="alert alert-info">You are not enrolled in any courses yet.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 col-12 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
