<?php
session_start();
require '../config/db.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to submit a review.");
}

// 2. Validate POST data
if (!isset($_POST['book_id'], $_POST['rating'], $_POST['comment'])) {
    die("Invalid form submission.");
}

$book_id = (int)$_POST['book_id'];
$user_id = (int)$_SESSION['user_id'];
$rating = (int)$_POST['rating'];
$comment = trim($_POST['comment']);

// Optional: validate rating range
if ($rating < 1 || $rating > 5) {
    die("Rating must be between 1 and 5.");
}

// 3. Insert review into database
try {
    $stmt = $pdo->prepare("INSERT INTO reviews (book_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$book_id, $user_id, $rating, $comment]);
} catch (PDOException $e) {
    die("Error saving review: " . $e->getMessage());
}

// 4. Redirect back to the book page
header("Location: ../frontend/book.php?id=$book_id");
exit;
?>
