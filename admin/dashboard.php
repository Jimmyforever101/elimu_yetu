<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/config.php';
include 'header.php';
// Export analytics as CSV
if (isset($_GET['export_analytics'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="analytics.csv"');
    $out = fopen('php://output', 'w');
    // Write headers
    fputcsv($out, ['Metric', 'Value']);
    // Total counts
    $total_students = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    $total_courses = $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    $total_lessons = $pdo->query('SELECT COUNT(*) FROM lessons')->fetchColumn();
    fputcsv($out, ['Total Students', $total_students]);
    fputcsv($out, ['Total Courses', $total_courses]);
    fputcsv($out, ['Total Lessons', $total_lessons]);
    // Course completion rates
    fputcsv($out, []);
    fputcsv($out, ['Course', 'Completed Lessons']);
    $completion_data = $pdo->query("SELECT c.title, COUNT(p.student_id)
    AS completed FROM courses c LEFT JOIN lessons l ON l.course_id = c.id LEFT JOIN progress p ON p.lesson_id = l.id AND p.status = 'Completed' GROUP BY c.id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($completion_data as $row) {
        fputcsv($out, [$row['title'], $row['completed']]);
    }
    fclose($out);
    exit;
}
$name = $_SESSION['admin_name'] ?? 'Admin';
$profile_pic = $_SESSION['profile_pic'] ?? '../assets/images/default_profile.png';
$total_students = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$total_courses = $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
$total_lessons = $pdo->query('SELECT COUNT(*) FROM lessons')->fetchColumn();
$recent_students = $pdo->query('SELECT name, email FROM students ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
$completion_data = $pdo->query("SELECT c.title, COUNT(p.student_id) AS completed FROM courses c LEFT JOIN lessons l ON l.course_id = c.id LEFT JOIN progress p ON p.lesson_id = l.id AND p.status = 'Completed' GROUP BY c.id")->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <div class="admin-content container mt-2">
    <div class="container mt-2">
            <h1>Admin Dashboard</h1>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Students</h5>
                            <span class="fs-2 text-primary"><?php echo $total_students; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Courses</h5>
                            <span class="fs-2 text-success"><?php echo $total_courses; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Lessons</h5>
                            <span class="fs-2 text-warning"><?php echo $total_lessons; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title" style="font-size:1.5rem;">Course Completion Rates</h5>
                            <canvas id="completionChart" height="128"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Recent Student Registrations</h5>
                            <ul class="list-group">
                                <?php foreach ($recent_students as $student): ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12 text-end">
                    <a href="dashboard.php?export_analytics=1" class="btn btn-info">Export Analytics (CSV)</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('completionChart').getContext('2d');
        const completionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($completion_data, 'title')); ?>,
                datasets: [{
                    label: 'Completed Lessons',
                    data: <?php echo json_encode(array_column($completion_data, 'completed')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

    </script>
</body>
</html>
<?php include 'footer.php'; ?>
