<?php

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

    http_response_code(401);
    $response['message'] = 'You must be logged in to manage favorites.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON input from the POST body
    $input = json_decode(file_get_contents('php://input'), true);
    $book_id = isset($input['book_id']) ? (int)$input['book_id'] : 0;

    $action = isset($input['action']) ? $input['action'] : 'add'; // Default to 'add' for backward compatibility

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
            $existing = $db->fetch($sql_check, [$user_id, $book_id]);

            if ($existing) {
                $response['success'] = true;
                $response['message'] = 'Book is already in your favorites.';
            } else {
                // Insert new favorite
                $sql_insert = "INSERT INTO favorites (user_id, book_id) VALUES (?, ?)";
                $db->execute($sql_insert, [$user_id, $book_id]);
                
                $response['success'] = true;
                $response['message'] = 'Book added to favorites successfully.';
            }
        } else if ($action === 'remove') {
            // Remove from favorites
            $sql_delete = "DELETE FROM favorites WHERE user_id = ? AND book_id = ?";
            $result = $db->execute($sql_delete, [$user_id, $book_id]);
            
            if ($result) {
                // Check if any rows were affected
                $sql_check_removed = "SELECT favorite_id FROM favorites WHERE user_id = ? AND book_id = ?";
                $still_exists = $db->fetch($sql_check_removed, [$user_id, $book_id]);
                
                if (!$still_exists) {
                    $response['success'] = true;
                    $response['message'] = 'Book removed from favorites.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Book was not in your favorites.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to remove from favorites.';
            }
        }
        
        // Get updated total favorites count
        $totalFavorites = $db->fetch("SELECT COUNT(*) as cnt FROM favorites WHERE user_id=?", [$user_id])['cnt'];

        $response['data'] = [
            'book_id' => $book_id,
            'action' => $action,
            'total_favorites' => $totalFavorites
        ];
        
        else {
            http_response_code(400);
            $response['message'] = 'Invalid action. Use "add" or "remove".';
        }

    } catch (Exception $e) {
        // Handle database errors
        error_log("Database error in favorites API: " . $e->getMessage());
        http_response_code(500);
        $response['message'] = 'Database error: Could not process favorite.';
    }

} else {
    // Only allow POST requests
    http_response_code(405);
    $response['message'] = 'Method Not Allowed.';
}

echo json_encode($response);
?>