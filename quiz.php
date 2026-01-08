<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'includes/config.php';
$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
$student_id = $_SESSION['student_id'];
// Fetch quiz for lesson
$stmt = $pdo->prepare('SELECT * FROM quizzes WHERE lesson_id = ?');
$stmt->execute([$lesson_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    echo '<div class="alert alert-info">No quiz available for this lesson.</div>';
    exit;
}
// Handle quiz submission
$score = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $correct = 0;
    $total = 0;
    // Fetch questions
    $stmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ?');
    $stmt->execute([$quiz['id']]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($questions as $q) {
        $total++;
        if (isset($answers[$q['id']]) && $answers[$q['id']] == $q['correct_option']) {
            $correct++;
        }
    }
    $score = round(($correct / max($total,1)) * 100);
    // Save result
    $stmt = $pdo->prepare('INSERT INTO quiz_results (student_id, quiz_id, score) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE score = VALUES(score)');
    $stmt->execute([$student_id, $quiz['id'], $score]);
    // Award badge for high score
    if ($score >= 80) {
        // Fetch the badge for this quiz
        $badge_stmt = $pdo->prepare('SELECT b.id FROM badges b WHERE b.title = CONCAT("Quiz Star: ", (SELECT title FROM quizzes WHERE id = ?))');
        $badge_stmt->execute([$quiz['id']]);
        $badge_row = $badge_stmt->fetch();
        if ($badge_row) {
            $badge_id = $badge_row['id'];
            $stmt = $pdo->prepare('SELECT 1 FROM student_badges WHERE student_id = ? AND badge_id = ?');
            $stmt->execute([$student_id, $badge_id]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO student_badges (student_id, badge_id) VALUES (?, ?)');
                $stmt->execute([$student_id, $badge_id]);
            }
        }
    }
}
// Fetch questions
$stmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ?');
$stmt->execute([$quiz['id']]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($quiz['title']); ?> | Quiz</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">STUDENT QUIZZES</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="achievements.php">Achievements</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
        <?php if ($score !== null): ?>
            <div class="alert alert-info">Your score: <?php echo $score; ?>%</div>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        <?php else: ?>
            <form method="post">
                <?php foreach ($questions as $q): ?>
                    <div class="mb-3">
                        <strong><?php echo htmlspecialchars($q['question']); ?></strong><br>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $i; ?>" id="q<?php echo $q['id']; ?>o<?php echo $i; ?>">
                                <label class="form-check-label" for="q<?php echo $q['id']; ?>o<?php echo $i; ?>">
                                    <?php echo htmlspecialchars($q['option_' . $i]); ?>
                                </label>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-success w-100 mt-3">Submit Quiz</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
