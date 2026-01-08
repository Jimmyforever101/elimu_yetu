<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/config.php';
$message = '';
// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_course'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        if ($title) {
            $stmt = $pdo->prepare('INSERT INTO courses (title, description) VALUES (?, ?)');
            $stmt->execute([$title, $description]);
            $message = 'Course added.';
        }
    } elseif (isset($_POST['edit_course'])) {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $stmt = $pdo->prepare('UPDATE courses SET title=?, description=? WHERE id=?');
        $stmt->execute([$title, $description, $id]);
        $message = 'Course updated.';
    } elseif (isset($_POST['bulk_delete']) && isset($_POST['course_ids'])) {
        $ids = $_POST['course_ids'];
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id IN ($in)");
        $stmt->execute($ids);
        $message = 'Selected courses deleted.';
    } elseif (isset($_POST['import_csv'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) >= 2) {
                    $stmt = $pdo->prepare('INSERT INTO courses (title, description) VALUES (?, ?)');
                    $stmt->execute([$row[0], $row[1]]);
                }
            }
            fclose($file);
            $message = 'Courses imported.';
        }
    }
}
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM courses WHERE id=?');
    $stmt->execute([$id]);
    $message = 'Course deleted.';
}
// Export CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="courses.csv"');
    $out = fopen('php://output', 'w');
    $stmt = $pdo->query('SELECT title, description FROM courses');
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
    $where = 'WHERE title LIKE ? OR description LIKE ?';
    $params = ["%$search%", "%$search%"];
}
// Advanced filters
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
if ($filter_date) {
    $where .= ($where ? ' AND ' : 'WHERE ') . 'DATE(created_at) = ?';
    $params[] = $filter_date;
}
// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM courses $where");
$count_stmt->execute($params);
$total_courses = $count_stmt->fetchColumn();
$total_pages = ceil($total_courses / $limit);
$stmt = $pdo->prepare("SELECT * FROM courses $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch course for editing
$edit_course = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE id=?');
    $stmt->execute([$id]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
    <div class="container mt-2">
            <h2>Manage Courses</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form class="mb-3" method="get" action="">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="filter_date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-md-6">
                    <h4><?php echo $edit_course ? 'Edit Course' : 'Add New Course'; ?></h4>
                    <form method="post" action="">
                        <?php if ($edit_course): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_course['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required value="<?php echo $edit_course['title'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"><?php echo $edit_course['description'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" name="<?php echo $edit_course ? 'edit_course' : 'add_course'; ?>" class="btn btn-primary">
                            <?php echo $edit_course ? 'Update Course' : 'Add Course'; ?>
                        </button>
                        <?php if ($edit_course): ?>
                            <a href="courses.php" class="btn btn-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                    <hr>
                    <h4>Import Courses (CSV)</h4>
                    <form method="post" enctype="multipart/form-data" action="">
                        <input type="file" name="csv_file" accept=".csv" required>
                        <button type="submit" name="import_csv" class="btn btn-success">Import</button>
                    </form>
                    <a href="courses.php?export=1" class="btn btn-info mt-2">Export Courses (CSV)</a>
                </div>
                <div class="col-md-6">
                    <h4>All Courses</h4>
                    <form method="post" action="">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all" onclick="for(let cb of document.querySelectorAll('.select_course'))cb.checked=this.checked;"></th>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><input type="checkbox" name="course_ids[]" value="<?php echo $course['id']; ?>" class="select_course"></td>
                                        <td><?php echo $course['id']; ?></td>
                                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                                        <td><?php echo htmlspecialchars($course['description']); ?></td>
                                        <td>
                                            <a href="courses.php?edit=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="courses.php?delete=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="bulk_delete" class="btn btn-danger" onclick="return confirm('Delete selected courses?');">Bulk Delete</button>
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
        </div>
    </div>
</body>
</html>
    <?php include 'footer.php'; ?>
