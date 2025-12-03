<?php
// Login authentication handler

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
$db = Database::getInstance();


$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['username']);
    $password = $_POST['password'];

    //$stmt = $pdo->prepare("SELECT user_id, password_hash, full_name FROM users WHERE full_name = ?");
    //$stmt->execute([$login_identifier]);
    //$user = $stmt->fetch();
    
    $sql = "SELECT user_id, password_hash, full_name FROM users WHERE full_name = ?";
    $user = $db->fetch($sql, [$login_identifier]);


    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        
        $sql_log = "INSERT INTO activity_log (user_id, activity_type, description)
            VALUES (?, 'login', 'User logged in')";
        //$db->execute($sql_log, [$user_id]);
        $db->execute($sql_log, [$user['user_id']]);

        header('Location: ../frontend/index.php'); // redirect to homepage
        exit;
    } else {
        $message = "Invalid name or password.";
    }
}
?>