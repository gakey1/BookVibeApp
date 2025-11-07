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
            
            // Format for template consistency
            $formattedBooks = [];
            foreach ($books as $book) {
                $formattedBooks[] = [
                    'title' => $book['title'],
                    'author' => $book['author'], 
                    'cover_image' => 'assets/images/books/' . $book['cover_image'],
                    'genre_name' => $book['genre_name']
                ];
            }
            
            return $formattedBooks;
            
        } catch (Exception $e) {
            // Return empty array on database error
            return [];
        }
    }
}
?>