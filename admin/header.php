<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
// admin header
$activePage = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$profilePic = $_SESSION['profile_pic'] ?? '../assets/images/default_profile.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimu Yetu Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <style>
        .admin-sidebar {
            min-width: 240px;
            background: linear-gradient(135deg, #1976d2 0%, #90caf9 100%);
            border-right: 1px solid #e0e0e0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 18px; /* reduced from 30px */
            color: #fff;
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
        }
        .admin-sidebar .logo {
            font-size: 1.7rem;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 18px; /* reduced from 30px */
            text-align: center;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-sidebar .logo img {
            height: 38px;
            margin-right: 12px;
        }
        .admin-sidebar .admin-info {
            text-align: center;
            margin-bottom: 18px; /* reduced from 30px */
        }
        .admin-sidebar .admin-info img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
            border: 2px solid #fff;
        }
        .admin-sidebar .admin-info .name {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .admin-sidebar .nav {
            flex-direction: column;
            align-items: flex-start;
        }
        .admin-sidebar .nav-link {
            color: #fff;
            padding: 12px 24px;
            width: 100%;
            border-radius: 0;
            font-size: 1.1rem;
            transition: background 0.2s;
        }
        .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-weight: bold;
        }
        .admin-content {
            margin-left: 240px;
            padding: 18px 16px; /* reduced from 30px 20px */
            background: #f8fafc;
            min-height: 100vh;
        }
        @media (max-width: 768px) {
            .admin-sidebar { position: static; min-width: 100%; height: auto; }
            .admin-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <div class="logo mb-4">
            <img src="../assets/images/logo.png" alt=""> System Admin
        </div>
        <div class="admin-info mb-4">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Admin Profile">
            <div class="name">Welcome, <?php echo htmlspecialchars($adminName); ?></div>
        </div>
        <nav>
            <ul class="nav">
                <li class="nav-item"><a class="nav-link<?php echo ($activePage == 'dashboard.php') ? ' active' : ''; ?>" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link<?php echo ($activePage == 'courses.php') ? ' active' : ''; ?>" href="courses.php">Manage Courses</a></li>
                <li class="nav-item"><a class="nav-link<?php echo ($activePage == 'lessons.php') ? ' active' : ''; ?>" href="lessons.php">Manage Lessons</a></li>
                <li class="nav-item"><a class="nav-link<?php echo ($activePage == 'quizzes.php') ? ' active' : ''; ?>" href="quizzes.php">Manage Quizzes</a></li>
                <li class="nav-item"><a class="nav-link<?php echo ($activePage == 'students.php') ? ' active' : ''; ?>" href="students.php">Manage Students</a></li>
                <li class="nav-item"><a class="nav-link<?php echo ($activePage == 'roles.php') ? ' active' : ''; ?>" href="roles.php">User Roles</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="admin-content">
