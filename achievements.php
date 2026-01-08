<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
$student_id = $_SESSION['student_id'];
// Fetch badges
$stmt = $pdo->prepare('SELECT b.title, b.icon_url, sb.date_awarded FROM badges b JOIN student_badges sb ON b.id = sb.badge_id WHERE sb.student_id = ? ORDER BY sb.date_awarded DESC');
$stmt->execute([$student_id]);
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Achievements</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?><br><br>
    <div class="container mt-5">
        <h2>My Achievements</h2>
        <?php
        // Check for course completion badges
        $completion_sql = 'SELECT c.id, c.title, COUNT(DISTINCT l.id) AS total_lessons, COUNT(DISTINCT q.id) AS total_quizzes
            FROM courses c
            LEFT JOIN lessons l ON l.course_id = c.id
            LEFT JOIN quizzes q ON q.lesson_id = l.id
            JOIN enrollments e ON e.course_id = c.id AND e.student_id = ?
            GROUP BY c.id';
        $completion_stmt = $pdo->prepare($completion_sql);
        $completion_stmt->execute([$student_id]);
        echo '<div class="row g-3 mb-4">';
        $completion_badge_count = 0;
        while ($course = $completion_stmt->fetch(PDO::FETCH_ASSOC)) {
            // Count completed lessons
            $lessons_sql = 'SELECT COUNT(*) FROM progress p JOIN lessons l ON p.lesson_id = l.id WHERE p.student_id = ? AND l.course_id = ? AND p.status = "Completed"';
            $lessons_stmt = $pdo->prepare($lessons_sql);
            $lessons_stmt->execute([$student_id, $course['id']]);
            $completed_lessons = $lessons_stmt->fetchColumn();
            // Count quizzes with 100% score
            $quizzes_sql = 'SELECT COUNT(*) FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id JOIN lessons l ON q.lesson_id = l.id WHERE qr.student_id = ? AND l.course_id = ? AND qr.score = 100';
            $quizzes_stmt = $pdo->prepare($quizzes_sql);
            $quizzes_stmt->execute([$student_id, $course['id']]);
            $completed_quizzes = $quizzes_stmt->fetchColumn();
            if ($completed_lessons == $course['total_lessons'] && $completed_quizzes == $course['total_quizzes'] && $course['total_lessons'] > 0) {
                $completion_badge_count++;
                echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
                echo '<div class="card shadow-sm h-100">';
                echo '<div class="card-body text-center">';
                echo '<img src="assets/images/completion_badge.png" alt="Course Completion Badge" style="width:48px;height:48px;vertical-align:middle;margin-bottom:10px;">';
                echo '<h6 class="mt-2 mb-1 text-success">Course Completion Badge</h6>';
                echo '<div class="fw-bold mb-1" style="font-size:1rem;">' . htmlspecialchars($course['title']) . '</div>';
                echo '<span class="badge bg-success mb-2">Completed</span>';
                echo '<div class="text-muted" style="font-size:0.95rem;">All lessons and quizzes finished!</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';
        if ($completion_badge_count === 0) {
            echo '<div class="alert alert-info">No achievements yet. Keep learning to earn badges!</div>';
        }
        ?>

        <hr>
        <h4 class="mt-4">My Quiz Scores</h4>
    <div class="table-responsive">
    <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Lesson</th>
                    <th>Course</th>
                    <th>Score (%)</th>
                    <th>Badge Earned</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = 'SELECT qr.score, q.title AS quiz_title, l.title AS lesson_title, c.title AS course_title, qr.quiz_id
                    FROM quiz_results qr
                    JOIN quizzes q ON qr.quiz_id = q.id
                    JOIN lessons l ON q.lesson_id = l.id
                    JOIN courses c ON l.course_id = c.id
                    WHERE qr.student_id = ?
                    ORDER BY qr.quiz_id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$student_id]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['quiz_title']) . '</td>';
                echo '<td>' . htmlspecialchars($row['lesson_title']) . '</td>';
                echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
                echo '<td>' . htmlspecialchars($row['score']) . '</td>';
                // Show badge only if score is 100
                if ($row['score'] == 100) {
                    echo '<td><span class="badge bg-success"><img src="assets/images/logo.jpg" alt="Badge" style="width:24px;height:24px;vertical-align:middle;margin-right:5px;"> 100% Badge</span></td>';
                } else {
                    echo '<td>-</td>';
                }
                echo '</tr>';
            }
            ?>
            </tbody>
    </table>
    </div>

        <?php if (isset($_GET['badge']) && $_GET['badge'] === 'earned'): ?>
            <div class="alert alert-success" style="margin-top:20px;">Congratulations! You have earned a new badge for completing all lessons in this course.</div>
        <?php endif; ?>
    </div>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
