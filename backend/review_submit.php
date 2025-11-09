<?php
// REVIEW SUBMISSION API LOGIC (Adapted for Frontend JSON Submission)

// Include database connection (this ensures $pdo is available)
require '../config/db.php'; 


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

>>>>>>> b8696e1a5a10513beff36c4b272bc18476960997
// Set JSON header for API response
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); 
    $response['message'] = 'You must be logged in to submit a review.';
    echo json_encode($response);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Read and Validate JSON Input
$input = json_decode(file_get_contents('php://input'), true);

$book_id = isset($input['book_id']) ? (int)$input['book_id'] : 0;
$rating = isset($input['rating']) ? (int)$input['rating'] : 0;
$comment = isset($input['review_text']) ? trim($input['review_text']) : ''; // Assuming frontend uses 'review_text'

if ($book_id <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
    http_response_code(400);
    $response['message'] = 'Invalid book ID, rating, or empty review.';
    echo json_encode($response);
    exit;
}

// Prevent Duplicates
// Check if the user has already reviewed this book (Optional)
$stmt_check = $pdo->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND book_id = ?");
$stmt_check->execute([$user_id, $book_id]);
if ($stmt_check->fetch()) {
    http_response_code(409); 
    $response['message'] = 'You have already reviewed this book.';
    echo json_encode($response);
    exit;
}

// Insert Review into Database
try {
    $stmt = $pdo->prepare("INSERT INTO reviews (book_id, user_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$book_id, $user_id, $rating, $comment]);

    $response['success'] = true;
    $response['message'] = 'Review successfully submitted!';
    http_response_code(200);

} catch (PDOException $e) {
    error_log("Review insertion failed: " . $e->getMessage());
    http_response_code(500);
    $response['message'] = 'A server error occurred during submission.';
}

echo json_encode($response);
exit;
?>
