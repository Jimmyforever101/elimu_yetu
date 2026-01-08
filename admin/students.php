<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/config.php';
$message = '';
// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM students WHERE id=?');
    $stmt->execute([$id]);
    $message = 'Student deleted.';
}
// Handle edit
// Set $edit_student for both edit and update actions
$edit_student = null;
if (isset($_GET['edit']) || isset($_GET['update'])) {
    $id = isset($_GET['edit']) ? $_GET['edit'] : $_GET['update'];
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id=?');
    $stmt->execute([$id]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $stmt = $pdo->prepare('UPDATE students SET name=?, email=?, phone=? WHERE id=?');
    $stmt->execute([$name, $email, $phone, $id]);
    $message = 'Student updated.';
    $edit_student = null;
}
// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete']) && isset($_POST['student_ids'])) {
    $ids = $_POST['student_ids'];
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM students WHERE id IN ($in)");
    $stmt->execute($ids);
    $message = 'Selected students deleted.';
}
// Handle import CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 3) {
                $stmt = $pdo->prepare('INSERT INTO students (name, email, profile_pic) VALUES (?, ?, ?)');
                $stmt->execute([$row[0], $row[1], $row[2]]);
            }
        }
        fclose($file);
        $message = 'Students imported.';
    }
}
// Export CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students.csv"');
    $out = fopen('php://output', 'w');
    $stmt = $pdo->query('SELECT name, email, profile_pic FROM students');
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];
if ($search) {
    $where = 'WHERE name LIKE ? OR email LIKE ?';
    $params = ["%$search%", "%$search%"];
}
// Advanced filters
$filter_course = isset($_GET['filter_course']) ? intval($_GET['filter_course']) : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
if ($filter_course) {
    $where .= ($where ? ' AND ' : 'WHERE ') . 'id IN (SELECT student_id FROM progress WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = ?))';
    $params[] = $filter_course;
}
if ($filter_date) {
    $where .= ($where ? ' AND ' : 'WHERE ') . 'DATE(timestamp) = ?';
    $params[] = $filter_date;
}
// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
$count_stmt->execute($params);
$total_students = $count_stmt->fetchColumn();
$total_pages = ceil($total_students / $limit);
$stmt = $pdo->prepare("SELECT * FROM students $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?><!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
<div class="admin-content container mt-2">
            <h2>Manage Students</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form class="mb-3" method="get" action="">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="filter_course" class="form-control">
                            <option value="">Filter by Course</option>
                            <?php $courses = $pdo->query('SELECT * FROM courses ORDER BY title')->fetchAll(PDO::FETCH_ASSOC); foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php if ($filter_course == $course['id']) echo 'selected'; ?>><?php echo htmlspecialchars($course['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="filter_date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-md-6">
                    <h4>Import Students (CSV)</h4>
                    <form method="post" enctype="multipart/form-data" action="">
                        <input type="file" name="csv_file" accept=".csv" required>
                        <button type="submit" name="import_csv" class="btn btn-success">Import</button>
                    </form>
                    <a href="students.php?export=1" class="btn btn-info mt-2">Export Students (CSV)</a>
                </div>
                <div class="col-md-6">
                    <?php if ($edit_student): ?>
                        <?php if (isset($_GET['update'])): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4>Update Student Information</h4>
                                <form method="post" action="">
                                    <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_student['name']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($edit_student['email']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" required pattern="[0-9+\- ]{7,20}" title="Enter a valid phone number" value="<?php echo htmlspecialchars($edit_student['phone']); ?>">
                                    </div>
                                    <button type="submit" name="edit_student" class="btn btn-primary">Update Student</button>
                                    <a href="students.php" class="btn btn-secondary ms-2">Cancel</a>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h4>Edit Student</h4>
                                <form method="post" action="">
                                    <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_student['name']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($edit_student['email']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" required pattern="[0-9+\- ]{7,20}" title="Enter a valid phone number" value="<?php echo htmlspecialchars($edit_student['phone']); ?>">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <form method="post" action="">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select_all" onclick="for(let cb of document.querySelectorAll('.select_student'))cb.checked=this.checked;"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" class="select_student"></td>
                                <td><?php echo $student['id']; ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td>
                                    <a href="students.php?edit=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="students.php?update=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary ms-1">Update</a>
                                    <a href="students.php?delete=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?');">Delete</a>
                                    <a href="student_scores.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="bulk_delete" class="btn btn-danger" onclick="return confirm('Delete selected students?');">Bulk Delete</button>
            </form>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
    <script src="../assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>
