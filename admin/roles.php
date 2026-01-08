<?php
// User Roles Management Page
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
// Only allow admins to access this page
$stmt = $pdo->prepare('SELECT role FROM admins WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
$role = $stmt->fetchColumn();
if ($role !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Fetch all admins and their roles
$admins = $pdo->query('SELECT a.id, a.name, a.email, a.role, c.title AS course_title FROM admins a LEFT JOIN courses c ON a.course_id = c.id ORDER BY a.id ASC')->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query('SELECT id, title FROM courses')->fetchAll(PDO::FETCH_ASSOC);

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $admin_id = intval($_POST['admin_id']);
    $role = $_POST['role'] === 'course_admin' ? 'course_admin' : 'admin';
    $course_id = ($role === 'course_admin') ? intval($_POST['course_id']) : null;
    $stmt = $pdo->prepare('UPDATE admins SET role = ?, course_id = ? WHERE id = ?');
    $stmt->execute([$role, $course_id, $admin_id]);
    header('Location: roles.php');
    exit;
}

// Handle new course admin creation
// Handle admin deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
    $admin_id = intval($_POST['admin_id']);
    // Prevent self-deletion
    if ($admin_id !== $_SESSION['admin_id']) {
        $stmt = $pdo->prepare('DELETE FROM admins WHERE id = ?');
        $stmt->execute([$admin_id]);
    }
    header('Location: roles.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course_admin'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $course_id = intval($_POST['course_id']);
    $profile_pic = '../assets/images/default_profile.png';
    if ($name && $email && $password && $course_id) {
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admins (name, email, password, profile_pic, role, course_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hashed_password, $profile_pic, 'course_admin', $course_id]);
        }
    }
    header('Location: roles.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Roles Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>User Roles Management</h2>
            <div>
                <a href="register.php" class="btn btn-primary me-2">Admin Registration</a>
                <a href="dashboard.php" class="btn btn-secondary">Return to Dashboard</a>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-3">Admins & Roles</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Course</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo $admin['role'] === 'course_admin' ? 'Course Admin' : 'Admin'; ?></td>
                            <td><?php echo $admin['course_title'] ?? '-'; ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                    <select name="role" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                        <option value="admin" <?php if($admin['role']==='admin'||!$admin['role']) echo 'selected'; ?>>Admin</option>
                                        <option value="course_admin" <?php if($admin['role']==='course_admin') echo 'selected'; ?>>Course Admin</option>
                                    </select>
                                    <?php if($admin['role']==='course_admin'): ?>
                                        <select name="course_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?php echo $course['id']; ?>" <?php if($admin['course_title']===$course['title']) echo 'selected'; ?>><?php echo htmlspecialchars($course['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                    <button type="submit" name="update_role" class="btn btn-sm btn-primary ms-2">Update</button>
                                </form>
                                <?php if ($admin['id'] !== $_SESSION['admin_id']): ?>
                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" name="delete_admin" class="btn btn-sm btn-danger ms-2">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Add Course Admin</h4>
                <form method="post">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Name" required>
                        </div>
                        <div class="col-md-3">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-md-3">
                            <select name="course_id" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_course_admin" class="btn btn-success mt-3">Add Course Admin</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
