<?php
// Favorites Management API - Add/Remove favorites

// Define app constant for config access
define('BOOKVIBE_APP', true);

// Start the session to check user login status
session_start();

// Include database connection
require_once __DIR__ . '/../../config/db.php'; 

// Set default response header to JSON
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Authentication Check (MUST BE LOGGED IN)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to manage favorites.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON input from the POST body
    $input = json_decode(file_get_contents('php://input'), true);
    $book_id = isset($input['book_id']) ? (int)$input['book_id'] : 0;
    $action = isset($input['action']) ? $input['action'] : 'add';

    if ($book_id <= 0) {
        http_response_code(400);
        $response['message'] = 'Invalid book ID provided.';
        echo json_encode($response);
        exit;
    }

    try {
        if ($action === 'add') {
            // Check if already favorited
            $sql_check = "SELECT favorite_id FROM favorites WHERE user_id = ? AND book_id = ?";
            $stmt = $pdo->prepare($sql_check);
            $stmt->execute([$user_id, $book_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                $response['success'] = true;
                $response['message'] = 'Book is already in your favorites.';
            } else {
                // Insert new favorite
                $sql_insert = "INSERT INTO favorites (user_id, book_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql_insert);
                $stmt->execute([$user_id, $book_id]);
                
                $response['success'] = true;
                $response['message'] = 'Book added to favorites successfully.';
            }
        } else if ($action === 'remove') {
            // Remove from favorites
            $sql_delete = "DELETE FROM favorites WHERE user_id = ? AND book_id = ?";
            $stmt = $pdo->prepare($sql_delete);
            $stmt->execute([$user_id, $book_id]);
            
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Book removed from favorites.';
            } else {
                $response['success'] = false;
                $response['message'] = 'Book was not in your favorites.';
            }
        } else {
            http_response_code(400);
            $response['message'] = 'Invalid action. Use "add" or "remove".';
        }

    } catch (PDOException $e) {
        error_log("Database error in favorites API: " . $e->getMessage());
        http_response_code(500);
        $response['message'] = 'Database error occurred.';
    }

} else {
    http_response_code(405);
    $response['message'] = 'Method Not Allowed.';
}

echo json_encode($response);
?>