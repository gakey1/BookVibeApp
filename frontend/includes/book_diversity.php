<?php
/**
 * Book Diversity Manager
 * Provides random book selection for decorative purposes
 */

if (!defined('BOOKVIBE_APP')) {
    die('Direct access not permitted');
}

class BookDiversityManager {
    private $db;
    private static $instance = null;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get random books for decorative purposes (auth pages, etc.)
     */
    public function getDecorativeBooks($limit = 3) {
        try {
            // Debug: Log what we're doing
            error_log("BookDiversityManager: Attempting to fetch $limit decorative books");
            
            $books = $this->db->fetchAll("
                SELECT 
                    book_id,
                    title, 
                    author,
                    cover_image, 
                    genre_name
                FROM books b
                JOIN genres g ON b.genre_id = g.genre_id  
                WHERE b.cover_image IS NOT NULL 
                ORDER BY RAND()
                LIMIT ?
            ", [$limit]);
            
            error_log("BookDiversityManager: Retrieved " . count($books) . " books from database");
            
            // Format for template consistency
            $formattedBooks = [];
            foreach ($books as $book) {
                $formattedBooks[] = [
                    'title' => $book['title'],
                    'author' => $book['author'], 
                    'cover' => $book['cover_image'], // Use 'cover' key for template compatibility
                    'genre_name' => $book['genre_name']
                ];
            }
            
            error_log("BookDiversityManager: Returning " . count($formattedBooks) . " formatted books");
            return $formattedBooks;
            
        } catch (Exception $e) {
            // Log the actual error
            error_log("BookDiversityManager Error: " . $e->getMessage());
            return [];
        }
    }
}
?>