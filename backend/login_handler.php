<?php
// Login authentication handler

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id, password_hash, full_name FROM users WHERE full_name = ?");
    $stmt->execute([$login_identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        header('Location: ../frontend/index.php'); // redirect to homepage
        exit;
    } else {
        $message = "Invalid name or password.";
    }
}
?>