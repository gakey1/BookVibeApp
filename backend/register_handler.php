<?php
// Registration logic handler  
// @Tracy I Separated your backend logic from presentation layer for separation of concerns

require_once '../config/db.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name_input = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Hey Tracy - Had to fix this: empty emails were causing duplicate key errors
    // Since email is optional but database requires NOT NULL, generate unique placeholder
    if (empty($email)) {
        $email = 'user_' . time() . '_' . rand(1000, 9999) . '@noemail.local';
    }

    if ($full_name_input === '' || $password === '') {
        $message = "Name and password are required.";
        $messageType = 'danger';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE full_name = ?");
        $stmt->execute([$full_name_input]);
        if ($stmt->fetch()) {
            $message = "Name already taken.";
            $messageType = 'danger';
        } else {
            // Insert user with hashed password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$full_name_input, $email, $password_hash]);
            $message = "Registration successful! You can now <a href='login.php' class='text-dark'><u>login</u></a>.";
            $messageType = 'success';
        }
    }
}
?>