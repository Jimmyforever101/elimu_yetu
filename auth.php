<?php
session_start();
require_once 'includes/config.php';

// Handle login
$login_error = '';
$admin_login_error = '';
if (isset($_POST['login'])) {
    $email = trim($_POST['login_email']);
    $password = trim($_POST['login_password']);
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'student';
    if ($user_type === 'admin') {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['profile_pic'] = $admin['profile_pic'];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $admin_login_error = 'Invalid admin email or password.';
        }
    } else {
        $stmt = $pdo->prepare('SELECT * FROM students WHERE email = ?');
        $stmt->execute([$email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $login_error = 'Invalid email or password.';
        }
    }
}

// Handle registration
$register_error = '';
$register_success = '';
$admin_register_error = '';
$admin_register_success = '';
if (isset($_POST['register'])) {
    $name = trim($_POST['register_name']);
    $email = trim($_POST['register_email']);
    $password = trim($_POST['register_password']);
    $phone = trim($_POST['register_phone']);
    if ($name && $email && $password && $phone) {
        $stmt = $pdo->prepare('SELECT id FROM students WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $register_error = 'Email already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO students (name, email, phone, password) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $phone, $hashed]);
            $register_success = 'Registration successful! You can now log in.';
        }
    } else {
        $register_error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register | Elimu Yetu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #e3f2fd 0%, #f8fafc 100%);
            min-height: 100vh;
        }
        .auth-container {
            max-width: 700px;
            margin: 24px auto 0 auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(25, 118, 210, 0.08);
            padding: 24px 18px 18px 18px;
        }
        .auth-title {
            color: #1976d2;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 32px;
            text-align: center;
        }
        .auth-card {
            border: none;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
            border-radius: 14px;
            padding: 28px 18px;
        }
        .auth-card h4 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .form-label {
            font-weight: 500;
            color: #1976d2;
        }
        .btn-primary, .btn-success {
            border-radius: 20px;
            font-size: 1.1rem;
            padding: 10px 0;
        }
        .alert {
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .auth-container {
                padding: 18px 6px;
            }
            .auth-card {
                padding: 18px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-title">
            <img src="assets/images/logo.jpg" alt="Logo" style="height:38px;vertical-align:middle;margin-right:10px;"> Elimu Yetu Login / Register
        </div>
        <div class="d-flex justify-content-center mb-4">
            <button id="showLogin" class="btn btn-outline-primary me-2">Login</button>
            <button id="showRegister" class="btn btn-outline-success">Register</button>
        </div>
        <div id="loginForm" class="auth-card" style="display:block;">
            <h4>Login</h4>
            <?php if ($login_error): ?>
                <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <?php if ($admin_login_error): ?>
                <div class="alert alert-danger"><?php echo $admin_login_error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="login_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="login_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Login as</label>
                    <select name="user_type" class="form-select">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
        <div id="registerForm" class="auth-card" style="display:none;">
            <h4>Register</h4>
            <?php if ($register_error): ?>
                <div class="alert alert-danger"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <?php if ($register_success): ?>
                <div class="alert alert-success"><?php echo $register_success; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="register_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="register_email" class="form-control" required>
                </div>
                <div class="mb-3" id="phoneField">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="register_phone" class="form-control" required pattern="[0-9+\- ]{7,20}" title="Enter a valid phone number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="register_password" class="form-control" required>
                </div>
                <button type="submit" name="register" class="btn btn-success w-100">Register</button>
            </form>
        </div>
    </div>
    <script>
        const showLoginBtn = document.getElementById('showLogin');
        const showRegisterBtn = document.getElementById('showRegister');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        showLoginBtn.onclick = function() {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            showLoginBtn.classList.add('active');
            showRegisterBtn.classList.remove('active');
        };
        showRegisterBtn.onclick = function() {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            showRegisterBtn.classList.add('active');
            showLoginBtn.classList.remove('active');
        };
    // Removed admin registration toggle
    </script>
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
