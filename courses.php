<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
$student_id = $_SESSION['student_id'];
// Fetch all courses
$stmt = $pdo->query('SELECT * FROM courses');
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch enrolled course ids
$stmt = $pdo->prepare('SELECT course_id FROM enrollments WHERE student_id = ?');
$stmt->execute([$student_id]);
$enrolled_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'course_id');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Courses | Elimu Yetu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br>
    <br class="d-block d-sm-none">
    <div class="container pt-5" style="padding-top:120px;">
        <h2>All Courses</h2>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 col-12 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Details</a>
                            <?php if (in_array($course['id'], $enrolled_ids)): ?>
                                <span class="badge bg-success ms-2">Enrolled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2">Not Enrolled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
