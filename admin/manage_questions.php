<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/config.php';
include 'header.php';
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if (!$quiz_id) {
    echo '<div class="alert alert-danger">No quiz selected.</div>';
    include 'footer.php';
    exit;
}
// Handle add question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question = trim($_POST['question']);
    $option_1 = trim($_POST['option_1']);
    $option_2 = trim($_POST['option_2']);
    $option_3 = trim($_POST['option_3']);
    $option_4 = trim($_POST['option_4']);
    $correct_option = intval($_POST['correct_option']);
    if ($question && $option_1 && $option_2 && $option_3 && $option_4 && $correct_option) {
        $stmt = $pdo->prepare('INSERT INTO quiz_questions (quiz_id, question, option_1, option_2, option_3, option_4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$quiz_id, $question, $option_1, $option_2, $option_3, $option_4, $correct_option]);
    }
}

// Handle edit question
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE id = ?');
    $stmt->execute([$edit_id]);
    $edit_question = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $id = intval($_POST['id']);
    $question = trim($_POST['question']);
    $option_1 = trim($_POST['option_1']);
    $option_2 = trim($_POST['option_2']);
    $option_3 = trim($_POST['option_3']);
    $option_4 = trim($_POST['option_4']);
    $correct_option = intval($_POST['correct_option']);
    if ($question && $option_1 && $option_2 && $option_3 && $option_4 && $correct_option) {
        $stmt = $pdo->prepare('UPDATE quiz_questions SET question=?, option_1=?, option_2=?, option_3=?, option_4=?, correct_option=? WHERE id=?');
        $stmt->execute([$question, $option_1, $option_2, $option_3, $option_4, $correct_option, $id]);
        header('Location: manage_questions.php?quiz_id=' . $quiz_id);
        exit;
    }
}
// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare('DELETE FROM quiz_questions WHERE id=?');
    $stmt->execute([$id]);
    header('Location: manage_questions.php?quiz_id=' . $quiz_id);
    exit;
}
// Fetch questions
$stmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ?');
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
<div class="admin-content">
    <div class="container mt-4">
        <h2>Manage Questions for Quiz #<?php echo $quiz_id; ?></h2>
        <?php if (isset($edit_question)): ?>
        <form method="post" class="mb-4">
            <input type="hidden" name="id" value="<?php echo $edit_question['id']; ?>">
            <div class="row g-2">
                <div class="col-md-12 mb-2">
                    <input type="text" name="question" class="form-control" placeholder="Question" value="<?php echo htmlspecialchars($edit_question['question']); ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_1" class="form-control" placeholder="Option 1" value="<?php echo htmlspecialchars($edit_question['option_1']); ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_2" class="form-control" placeholder="Option 2" value="<?php echo htmlspecialchars($edit_question['option_2']); ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_3" class="form-control" placeholder="Option 3" value="<?php echo htmlspecialchars($edit_question['option_3']); ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_4" class="form-control" placeholder="Option 4" value="<?php echo htmlspecialchars($edit_question['option_4']); ?>" required>
                </div>
                <div class="col-md-3 mt-2">
                    <select name="correct_option" class="form-select" required>
                        <option value="">Correct Option</option>
                        <option value="1" <?php if($edit_question['correct_option']==1) echo 'selected'; ?>>Option 1</option>
                        <option value="2" <?php if($edit_question['correct_option']==2) echo 'selected'; ?>>Option 2</option>
                        <option value="3" <?php if($edit_question['correct_option']==3) echo 'selected'; ?>>Option 3</option>
                        <option value="4" <?php if($edit_question['correct_option']==4) echo 'selected'; ?>>Option 4</option>
                    </select>
                </div>
                <div class="col-md-3 mt-2">
                    <button type="submit" name="update_question" class="btn btn-primary w-100">Update Question</button>
                    <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </form>
        <?php else: ?>
        <form method="post" class="mb-4">
            <div class="row g-2">
                <div class="col-md-12 mb-2">
                    <input type="text" name="question" class="form-control" placeholder="Question" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_1" class="form-control" placeholder="Option 1" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_2" class="form-control" placeholder="Option 2" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_3" class="form-control" placeholder="Option 3" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="option_4" class="form-control" placeholder="Option 4" required>
                </div>
                <div class="col-md-3 mt-2">
                    <select name="correct_option" class="form-select" required>
                        <option value="">Correct Option</option>
                        <option value="1">Option 1</option>
                        <option value="2">Option 2</option>
                        <option value="3">Option 3</option>
                        <option value="4">Option 4</option>
                    </select>
                </div>
                <div class="col-md-3 mt-2">
                    <button type="submit" name="add_question" class="btn btn-success w-100">Add Question</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="mb-3">All Questions</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Options</th>
                            <th>Correct</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars($q['question']); ?></td>
                            <td>
                                1. <?php echo htmlspecialchars($q['option_1']); ?><br>
                                2. <?php echo htmlspecialchars($q['option_2']); ?><br>
                                3. <?php echo htmlspecialchars($q['option_3']); ?><br>
                                4. <?php echo htmlspecialchars($q['option_4']); ?>
                            </td>
                            <td><?php echo $q['correct_option']; ?></td>
                            <td>
                                <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>&edit=<?php echo $q['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>&delete=<?php echo $q['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?');">Delete</a>
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
