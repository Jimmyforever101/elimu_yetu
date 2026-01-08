<?php
// Database configuration
$host = 'localhost';
$db   = 'track_db'; // Change to your actual database name
$user = 'root';    // Change if your MySQL user is different
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
