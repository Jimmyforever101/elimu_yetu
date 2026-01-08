<?php
// Admin login page
session_start();
require_once '../includes/config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password'])) {
        // Login successful
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['profile_pic'] = $admin['profile_pic'];
        header('Location: dashboard.php');
        exit;
    } else {
        $message = 'Invalid email or password.';
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #e3f2fd 0%, #f8fafc 100%);
            min-height: 100vh;
        }
        .login-container {
            max-width: 400px;
            margin: 60px auto;
        }
        .card {
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(25, 118, 210, 0.08);
        }
        .card-title {
            color: #1976d2;
            font-weight: bold;
            text-align: center;
        }
        .form-label {
            font-weight: 500;
            color: #1976d2;
        }
        .btn-primary {
            border-radius: 20px;
            font-size: 1.1rem;
            padding: 10px 0;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card p-4">
            <h2 class="card-title mb-4">Admin Login</h2>
            <?php if ($message): ?>
                <div class="alert alert-danger text-center"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    <script src="../assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
