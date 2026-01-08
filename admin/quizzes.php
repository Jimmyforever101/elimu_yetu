<?php
session_start();
require_once '../includes/config.php';
include 'header.php';

// Handle add quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quiz'])) {
    $title = trim($_POST['title']);
    $lesson_id = intval($_POST['lesson_id']);
    if ($title && $lesson_id) {
        // Check if a quiz already exists for this lesson
        $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM quizzes WHERE lesson_id = ?');
        $check_stmt->execute([$lesson_id]);
        $quiz_count = $check_stmt->fetchColumn();
        if ($quiz_count > 0) {
            echo '<div class="alert alert-danger mt-3">A quiz already exists for this lesson. Only one quiz per lesson is allowed.</div>';
        } else {
            $stmt = $pdo->prepare('INSERT INTO quizzes (title, lesson_id) VALUES (?, ?)');
            $stmt->execute([$title, $lesson_id]);
        }
    }
}

// Fetch lessons for dropdown
$lessons = $pdo->query('SELECT id, title FROM lessons')->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>

    <div class="admin-content container mt-2">
        <h2>Manage Quizzes</h2>
        <form method="post" class="mb-4">
            <div class="row g-2">
                <div class="col-md-4">
                    <select name="lesson_id" class="form-select" required>
                        <option value="">Select Lesson</option>
                        <?php foreach ($lessons as $lesson): ?>
                            <option value="<?php echo $lesson['id']; ?>"><?php echo htmlspecialchars($lesson['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="title" class="form-control" placeholder="Quiz Title" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_quiz" class="btn btn-success w-100">Add Quiz</button>
                </div>
            </div>
        </form>
        <!-- Existing code for listing/editing/deleting quizzes -->
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="mb-3">All Quizzes</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lesson</th>
                            <th>Title</th>
                            <th>Actions</th>
                            <th>Questions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $quizzes = $pdo->query('SELECT quizzes.*, lessons.title AS lesson_title FROM quizzes JOIN lessons ON quizzes.lesson_id = lessons.id ORDER BY quizzes.id DESC')->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($quizzes as $quiz): ?>
                            <tr>
                                <td><?php echo $quiz['id']; ?></td>
                                <td><?php echo htmlspecialchars($quiz['lesson_title']); ?></td>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td>
                                    <a href="quizzes.php?edit=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="quizzes.php?delete=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this quiz?');">Delete</a>
                                </td>
                                <td>
                                    <a href="manage_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-info">Manage Questions</a>
                                    <a href="quizzes.php?results=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary mt-1">View Results</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    // Handle delete
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $pdo->prepare('DELETE FROM quizzes WHERE id=?');
        $stmt->execute([$id]);
        echo '<script>window.location="quizzes.php";</script>';
    }
    // Handle edit
    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE id=?');
        $stmt->execute([$id]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($quiz): ?>
        <div class="card mt-4">
            <div class="card-body">
                <h4>Edit Quiz</h4>
                <form method="post">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                    <div class="mb-3">
                        <label>Quiz Title</label>
                        <input type="text" name="edit_title" class="form-control" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                    </div>
                    <button type="submit" name="update_quiz" class="btn btn-primary">Update Quiz</button>
                </form>
            </div>
        </div>
    <?php
        endif;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quiz'])) {
        $quiz_id = intval($_POST['quiz_id']);
        $edit_title = trim($_POST['edit_title']);
        if ($quiz_id && $edit_title) {
            $stmt = $pdo->prepare('UPDATE quizzes SET title=? WHERE id=?');
            $stmt->execute([$edit_title, $quiz_id]);
            echo '<script>window.location="quizzes.php";</script>';
        }
    }
    // Show results for each quiz
    if (isset($_GET['results'])) {
        $quiz_id = intval($_GET['results']);
        $stmt = $pdo->prepare('SELECT qr.*, s.name FROM quiz_results qr JOIN students s ON qr.student_id = s.id WHERE qr.quiz_id = ? ORDER BY qr.score DESC');
        $stmt->execute([$quiz_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<div class="card mt-4"><div class="card-body"><h4>Quiz Results</h4>';
        if (count($results) === 0) {
            echo '<div class="alert alert-info">No attempts yet.</div>';
        } else {
            echo '<table class="table"><thead><tr><th>Student Name</th><th>Quiz Score (%)</th></tr></thead><tbody>';
            foreach ($results as $r) {
                echo '<tr><td>'.htmlspecialchars($r['name']).'</td><td>'.$r['score'].'%</td></tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div></div>';
    }
    ?>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>