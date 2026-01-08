<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/config.php';
include 'header.php';
// Handle add lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lesson'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $course_id = intval($_POST['course_id']);
    if ($title && $content && $course_id) {
        $stmt = $pdo->prepare('INSERT INTO lessons (title, content, course_id) VALUES (?, ?, ?)');
        if ($stmt->execute([$title, $content, $course_id])) {
            $message = '<div class="alert alert-success">Lesson added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add lesson. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Please fill in all fields.</div>';
    }
}
// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM lessons WHERE id=?');
    $stmt->execute([$id]);
}

// Handle edit
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_lesson = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
    $edit_lesson->execute([$edit_id]);
    $edit_lesson = $edit_lesson->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_lesson'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $course_id = intval($_POST['course_id']);
    if ($title && $content && $course_id) {
        $stmt = $pdo->prepare('UPDATE lessons SET title = ?, content = ?, course_id = ? WHERE id = ?');
        $stmt->execute([$title, $content, $course_id, $id]);
        header('Location: lessons.php');
        exit;
    }
}
// Fetch courses for dropdown
$courses = $pdo->query('SELECT id, title FROM courses')->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html>
<head>
        <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: 'textarea[name="content"]',
            menubar: false,
            plugins: 'lists link image code',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code',
            branding: false,
            height: 300
        });
        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                tinymce.triggerSave();
            });
        });
    }
});
</script>
</head>
<body>
<div class="admin-content container mt-2">
    <?php if (isset($message)) echo $message; ?>
    <h2>Manage Lessons</h2>
    <?php if (isset($edit_lesson)): ?>
    <form method="post" class="mb-4">
        <input type="hidden" name="id" value="<?php echo $edit_lesson['id']; ?>">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="course_id" class="form-select" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php if($edit_lesson['course_id']==$course['id']) echo 'selected'; ?>><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="title" class="form-control" placeholder="Lesson Title" value="<?php echo htmlspecialchars($edit_lesson['title']); ?>" required>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-12">
                <textarea name="content" class="form-control" placeholder="Lesson Content" rows="6" required><?php echo htmlspecialchars($edit_lesson['content']); ?></textarea>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-2">
                <button type="submit" name="update_lesson" class="btn btn-warning w-100">Update Lesson</button>
                <a href="lessons.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </div>
        </div>
    </form>
    <?php else: ?>
    <form method="post" class="mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="course_id" class="form-select" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="title" class="form-control" placeholder="Lesson Title" required>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-12">
                <textarea name="content" class="form-control" placeholder="Lesson Content" rows="6" required></textarea>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-2">
                <button type="submit" name="add_lesson" class="btn btn-success w-100">Add Lesson</button>
            </div>
        </div>
    </form>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <h4>All Lessons</h4>
            <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $lessons = $pdo->query('SELECT lessons.*, courses.title AS course_title FROM lessons JOIN courses ON lessons.course_id = courses.id ORDER BY lessons.id DESC')->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($lessons as $lesson): ?>
                        <tr>
                            <td><?php echo $lesson['id']; ?></td>
                            <td><?php echo htmlspecialchars($lesson['course_title']); ?></td>
                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                            <td><?php echo isset($lesson['content']) ? htmlspecialchars($lesson['content']) : '<span class="text-danger">No content</span>'; ?></td>
                            <td>
                                <a href="lessons.php?edit=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="lessons.php?delete=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this lesson?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php include 'footer.php'; ?>
