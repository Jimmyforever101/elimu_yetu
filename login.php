<?php
session_start();
require_once 'includes/config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare('SELECT * FROM students WHERE email = ?');
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student && password_verify($password, $student['password'])) {
        // Login successful
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['profile_pic'] = $student['profile_pic'];
        header('Location: dashboard.php');
        exit;
    } else {
        $message = 'Invalid email or password.';
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>User Login</h2>
    <?php if ($message): ?>
        <div style="color:red;"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <input type="text" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>
