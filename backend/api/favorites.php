<?php
// Favourites Management Logic

// Start the session to check user login status
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db.php'; 
$db = Database::getInstance();

// Set default response header to JSON
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Authentication Check (MUST BE LOGGED IN)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    $response['message'] = 'You must be logged in to add favorites.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate and Get Book ID
// TODO: expect a POST request from the frontend AJAX call
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON input from the POST body
    $input = json_decode(file_get_contents('php://input'), true);
    $book_id = isset($input['book_id']) ? (int)$input['book_id'] : 0;

    if ($book_id <= 0) {
        http_response_code(400); // Bad Request
        $response['message'] = 'Invalid book ID provided.';
        echo json_encode($response);
        exit;
    }

    // Prevent Duplicates (Check if the favorite already exists)
    $sql_check = "SELECT favorite_id FROM favorites WHERE user_id = ? AND book_id = ?";
    $existing = $db->fetch($sql_check, [$user_id, $book_id]);

    if ($existing) {
        $response['success'] = true;
        $response['message'] = 'Book is already in your favorites.';
        echo json_encode($response);
        exit;
    }

    // Insert the New Favorite (Core Action)
    try {
        $sql_insert = "INSERT INTO favorites (user_id, book_id) VALUES (?, ?)";
        $db->execute($sql_insert, [$user_id, $book_id]);
        
        $response['success'] = true;
        $response['message'] = 'Book added to favorites successfully.';
        http_response_code(200);

    } catch (Exception $e) {
        // Handle database errors (e.g., if book_id somehow violates foreign key)
        http_response_code(500);
        $response['message'] = 'Database error: Could not add favorite.';
    }

} else {
    // Only allow POST requests
    http_response_code(405);
    $response['message'] = 'Method Not Allowed.';
}

echo json_encode($response);
?>