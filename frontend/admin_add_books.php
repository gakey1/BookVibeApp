<?php
/**
 * Admin Script to add 3 new books to BookVibe database
 * Access via browser: http://localhost/BookVibeApp/frontend/admin_add_books.php
 */

// Define app constant for config access
define('BOOKVIBE_APP', true);

// Database connection
require_once '../config/db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Books - BookVibe Admin</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .book { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
        h1 { color: #6b21a7; }
        .btn { background: #6b21a7; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #581c87; }
    </style>
</head>
<body>
    <div class="container">
        <h1> Add New Books to BookVibe Database</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = Database::getInstance();
                
                echo "<h2>Processing...</h2>\n";
                
                // Get genre IDs
                $adventureGenre = $db->fetch("SELECT genre_id FROM genres WHERE genre_name = 'Adventure'");
                $scifiGenre = $db->fetch("SELECT genre_id FROM genres WHERE genre_name = 'Sci-Fi'");
                
                if (!$adventureGenre || !$scifiGenre) {
                    throw new Exception("Required genres not found in database");
                }
                
                $adventureId = $adventureGenre['genre_id'];
                $scifiId = $scifiGenre['genre_id'];
                
                echo "<p>Found Adventure Genre ID: {$adventureId}</p>\n";
                echo "<p>Found Sci-Fi Genre ID: {$scifiId}</p>\n";
                
                // Book 1: Run by Sarah Armstrong
                $book1 = [
                    'title' => 'Run',
                    'author' => 'Sarah Armstrong',
                    'description' => 'A gripping survival story that follows a young runner who must navigate dangerous terrain and face life-threatening challenges. This big-hearted adventure explores themes of resilience, determination, and the human spirit\'s capacity to endure against all odds.',
                    'cover_image' => 'Run.jpg',
                    'genre_id' => $adventureId,
                    'publication_year' => 2023,
                    'page_count' => 340,
                    'avg_rating' => 4.2,
                    'review_count' => 3
                ];
                
                // Book 2: Red City by Marie Liu  
                $book2 = [
                    'title' => 'Red City',
                    'author' => 'Marie Liu',
                    'description' => 'Set in a dystopian future where cities are color-coded by social hierarchy, this thought-provoking sci-fi novel explores themes of inequality and resistance. When a young woman discovers the truth behind the Red City\'s facade, she must choose between safety and revolution.',
                    'cover_image' => 'Red_City.jpg',
                    'genre_id' => $scifiId,
                    'publication_year' => 2024,
                    'page_count' => 425,
                    'avg_rating' => 4.5,
                    'review_count' => 7
                ];
                
                // Book 3: All That We See Or Seem by Ken Liu
                $book3 = [
                    'title' => 'All That We See Or Seem',
                    'author' => 'Ken Liu',
                    'description' => 'A mind-bending exploration of reality and perception that blurs the lines between dreams and waking life. This speculative fiction masterpiece challenges readers to question the nature of existence and consciousness in a world where nothing is quite what it seems.',
                    'cover_image' => 'All_That_We_See_Or_Seem.jpg',
                    'genre_id' => $scifiId,
                    'publication_year' => 2023,
                    'page_count' => 380,
                    'avg_rating' => 4.7,
                    'review_count' => 12
                ];
                
                $books = [$book1, $book2, $book3];
                
                // Check if books already exist
                foreach ($books as $book) {
                    $existing = $db->fetch("SELECT title FROM books WHERE title = ? AND author = ?", [$book['title'], $book['author']]);
                    if ($existing) {
                        echo "<p class='error'> Book already exists: {$book['title']} by {$book['author']}</p>\n";
                        continue;
                    }
                    
                    // Insert book
                    $sql = "INSERT INTO books (title, author, description, cover_image, genre_id, publication_year, page_count, avg_rating, review_count) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $result = $db->execute($sql, [
                        $book['title'],
                        $book['author'], 
                        $book['description'],
                        $book['cover_image'],
                        $book['genre_id'],
                        $book['publication_year'],
                        $book['page_count'],
                        $book['avg_rating'],
                        $book['review_count']
                    ]);
                    
                    if ($result) {
                        $bookId = $db->lastInsertId();
                        echo "<div class='book success'> Successfully Added: <strong>{$book['title']}</strong> by {$book['author']} (ID: {$bookId})</div>\n";
                    } else {
                        echo "<div class='book error'> Failed to add: {$book['title']} by {$book['author']}</div>\n";
                    }
                }
                
                // Update genre book counts
                $db->execute("UPDATE genres SET book_count = (SELECT COUNT(*) FROM books WHERE genre_id = genres.genre_id)");
                echo "<p class='success'> Updated genre book counts</p>\n";
                
                echo "<h2 class='success'> Book addition process completed!</h2>\n";
                echo "<p><a href='browse.php'>View Books</a> | <a href='index.php'>Back to Homepage</a></p>\n";
                
            } catch (Exception $e) {
                echo "<div class='error'> Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
            }
        } else {
            ?>
            <p>This script will add the following 3 new books to your BookVibe database:</p>
            
            <div class="book">
                <h3> Run</h3>
                <p><strong>Author:</strong> Sarah Armstrong</p>
                <p><strong>Genre:</strong> Adventure</p>
                <p><strong>Description:</strong> A gripping survival story and big-hearted adventure about resilience and determination.</p>
            </div>
            
            <div class="book">
                <h3> Red City</h3>
                <p><strong>Author:</strong> Marie Liu</p>
                <p><strong>Genre:</strong> Sci-Fi</p>
                <p><strong>Description:</strong> A dystopian sci-fi novel exploring themes of inequality and resistance in a color-coded society.</p>
            </div>
            
            <div class="book">
                <h3> All That We See Or Seem</h3>
                <p><strong>Author:</strong> Ken Liu</p>
                <p><strong>Genre:</strong> Sci-Fi</p>
                <p><strong>Description:</strong> A mind-bending exploration of reality and perception that challenges our understanding of existence.</p>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn">Add These Books to Database</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>