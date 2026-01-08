<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/config.php';
// Export CSV for filtered student (must be before any output)
$filter_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;
if (isset($_GET['export']) && $filter_id) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_' . $filter_id . '_details.csv"');
    $out = fopen('php://output', 'w');
    // Scores
    fputcsv($out, ['Lesson', 'Course', 'Score (%)']);
    $sql = 'SELECT l.title AS lesson_title, c.title AS course_title, qr.score FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id JOIN lessons l ON q.lesson_id = l.id JOIN courses c ON l.course_id = c.id WHERE qr.student_id = ' . $filter_id;
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['lesson_title'], $row['course_title'], $row['score']]);
    }
    // Lessons Completed
    fputcsv($out, []); fputcsv($out, ['Lessons Completed']);
    fputcsv($out, ['Lesson', 'Course', 'Status']);
    $sql = 'SELECT l.title AS lesson_title, c.title AS course_title, p.status FROM progress p JOIN lessons l ON p.lesson_id = l.id JOIN courses c ON l.course_id = c.id WHERE p.student_id = ' . $filter_id . ' AND p.status = "Completed"';
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['lesson_title'], $row['course_title'], $row['status']]);
    }
    // Courses Enrolled
    fputcsv($out, []); fputcsv($out, ['Courses Enrolled']);
    fputcsv($out, ['Course', 'Enrolled At']);
    $sql = 'SELECT c.title AS course_title, e.enrolled_at FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = ' . $filter_id;
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['course_title'], $row['enrolled_at']]);
    }
    fclose($out);
    exit;
}
include 'header.php';
// Fetch all students
$students = $pdo->query('SELECT id, name, email FROM students')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Scores & Activities | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
<div class="admin-content container mt-2">
    <h2>Student Scores & Activities</h2>
    <form method="get" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="student_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Filter by Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" <?php if($filter_id==$student['id']) echo 'selected'; ?>><?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['email']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($filter_id): ?>
            <div class="col-md-2">
                <a href="student_scores.php?student_id=<?php echo $filter_id; ?>&export=1" class="btn btn-success">Export CSV</a>
            </div>
            <?php endif; ?>
        </div>
    </form>
    <div class="mb-4">
        <h4>Scores by Lesson</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Lesson</th>
                    <th>Course</th>
                    <th>Score (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT qr.student_id, qr.quiz_id, qr.score, l.title AS lesson_title, c.title AS course_title, s.name AS student_name FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id JOIN lessons l ON q.lesson_id = l.id JOIN courses c ON l.course_id = c.id JOIN students s ON qr.student_id = s.id';
                if ($filter_id) $sql .= ' WHERE qr.student_id = ' . $filter_id;
                $sql .= ' ORDER BY qr.student_id, qr.quiz_id';
                $stmt = $pdo->query($sql);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['lesson_title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['score']) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="mb-4">
        <h4>Lessons Completed</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Lesson</th>
                    <th>Course</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT p.student_id, p.lesson_id, p.status, l.title AS lesson_title, c.title AS course_title, s.name AS student_name FROM progress p JOIN lessons l ON p.lesson_id = l.id JOIN courses c ON l.course_id = c.id JOIN students s ON p.student_id = s.id WHERE p.status = "Completed"';
                if ($filter_id) $sql .= ' AND p.student_id = ' . $filter_id;
                $sql .= ' ORDER BY p.student_id, p.lesson_id';
                $stmt = $pdo->query($sql);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['lesson_title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="mb-4">
        <h4>Courses Enrolled</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT e.student_id, e.course_id, e.enrolled_at, s.name AS student_name, c.title AS course_title FROM enrollments e JOIN students s ON e.student_id = s.id JOIN courses c ON e.course_id = c.id';
                if ($filter_id) $sql .= ' WHERE e.student_id = ' . $filter_id;
                $sql .= ' ORDER BY e.student_id, e.course_id';
                $stmt = $pdo->query($sql);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['enrolled_at']) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php
// Export CSV for filtered student
if (isset($_GET['export']) && $filter_id) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_<?php echo $filter_id; ?>_details.csv"');
    $out = fopen('php://output', 'w');
    // Scores
    fputcsv($out, ['Lesson', 'Course', 'Score (%)']);
    $sql = 'SELECT l.title AS lesson_title, c.title AS course_title, qr.score FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id JOIN lessons l ON q.lesson_id = l.id JOIN courses c ON l.course_id = c.id WHERE qr.student_id = ' . $filter_id;
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['lesson_title'], $row['course_title'], $row['score']]);
    }
    // Lessons Completed
    fputcsv($out, []); fputcsv($out, ['Lessons Completed']);
    fputcsv($out, ['Lesson', 'Course', 'Status']);
    $sql = 'SELECT l.title AS lesson_title, c.title AS course_title, p.status FROM progress p JOIN lessons l ON p.lesson_id = l.id JOIN courses c ON l.course_id = c.id WHERE p.student_id = ' . $filter_id . ' AND p.status = "Completed"';
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['lesson_title'], $row['course_title'], $row['status']]);
    }
    // Courses Enrolled
    fputcsv($out, []); fputcsv($out, ['Courses Enrolled']);
    fputcsv($out, ['Course', 'Enrolled At']);
    $sql = 'SELECT c.title AS course_title, e.enrolled_at FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = ' . $filter_id;
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['course_title'], $row['enrolled_at']]);
    }
    fclose($out);
    exit;
}
include 'footer.php'; ?>
</body>
</html>
