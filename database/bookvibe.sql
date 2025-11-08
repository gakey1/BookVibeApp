-- BookVibe Database Schema
-- Created for INT1059 Assessment 2
-- Database: bookvibe

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bookvibe;
USE bookvibe;

-- Set charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist (for clean reset)
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS users;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    bio TEXT,
    favorite_genres JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    
    INDEX idx_email (email),
    INDEX idx_active (is_active)
);

-- ============================================
-- Table: genres
-- ============================================
CREATE TABLE genres (
    genre_id INT PRIMARY KEY AUTO_INCREMENT,
    genre_name VARCHAR(50) UNIQUE NOT NULL,
    genre_icon VARCHAR(50),
    display_order INT DEFAULT 0,
    book_count INT DEFAULT 0,
    
    INDEX idx_name (genre_name),
    INDEX idx_order (display_order)
);

-- ============================================
-- Table: books
-- ============================================
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publisher VARCHAR(255),
    publication_year INT,
    page_count INT,
    description TEXT,
    cover_image VARCHAR(255),
    genre_id INT,
    price DECIMAL(8,2),
    sale_price DECIMAL(8,2),
    avg_rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_author (author),
    INDEX idx_genre (genre_id),
    INDEX idx_rating (avg_rating),
    INDEX idx_year (publication_year),
    FULLTEXT idx_search (title, author, description)
);

-- ============================================
-- Table: reviews
-- ============================================
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(200),
    review_text TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_book (user_id, book_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_rating (rating),
    INDEX idx_public (is_public),
    INDEX idx_created (created_at)
);

-- ============================================
-- Table: favorites
-- ============================================
CREATE TABLE favorites (
    favorite_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, book_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_added (added_at)
);

-- ============================================
-- Table: sessions (for session management)
-- ============================================
CREATE TABLE sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);

-- ============================================
-- Views for optimized queries
-- ============================================

-- Book ratings summary view
CREATE OR REPLACE VIEW book_ratings AS
SELECT 
    b.book_id,
    b.title,
    b.author,
    b.cover_image,
    b.genre_id,
    g.genre_name,
    COUNT(r.review_id) as review_count,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star_count,
    SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star_count,
    SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star_count,
    SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star_count,
    SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star_count,
    ROUND((SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.review_id), 0)), 1) as five_star_percent,
    ROUND((SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.review_id), 0)), 1) as four_star_percent,
    ROUND((SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.review_id), 0)), 1) as three_star_percent,
    ROUND((SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.review_id), 0)), 1) as two_star_percent,
    ROUND((SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.review_id), 0)), 1) as one_star_percent
FROM books b
LEFT JOIN reviews r ON b.book_id = r.book_id AND r.is_public = TRUE
LEFT JOIN genres g ON b.genre_id = g.genre_id
GROUP BY b.book_id, b.title, b.author, b.cover_image, b.genre_id, g.genre_name;

-- User statistics view
CREATE OR REPLACE VIEW user_stats AS
SELECT 
    u.user_id,
    u.full_name,
    u.email,
    u.profile_picture,
    COUNT(DISTINCT r.review_id) as reviews_count,
    COUNT(DISTINCT f.favorite_id) as favorites_count,
    COALESCE(AVG(r.rating), 0) as avg_rating_given,
    COUNT(DISTINCT CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN r.review_id END) as reviews_this_month
FROM users u
LEFT JOIN reviews r ON u.user_id = r.user_id
LEFT JOIN favorites f ON u.user_id = f.user_id
WHERE u.is_active = TRUE
GROUP BY u.user_id, u.full_name, u.email, u.profile_picture;

-- ============================================
-- Insert sample genres
-- ============================================
INSERT INTO genres (genre_name, genre_icon, display_order) VALUES
('Fiction', 'fa-book', 1),
('Romance', 'fa-heart', 2),
('Thriller', 'fa-mask', 3),
('Sci-Fi', 'fa-rocket', 4),
('Fantasy', 'fa-dragon', 5),
('Mystery', 'fa-search', 6),
('Non-Fiction', 'fa-book-open', 7),
('Biography', 'fa-user', 8),
('History', 'fa-landmark', 9),
('Adventure', 'fa-compass', 10);

-- ============================================
-- Insert sample users for testing
-- ============================================
INSERT INTO users (full_name, email, password_hash, bio, favorite_genres) VALUES
('Sarah Mitchell', 'sarah@bookvibe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Passionate reader with a love for fantasy and romance novels. Always looking for my next great adventure in a book!', '["Fantasy", "Romance", "Sci-Fi"]'),
('Michael Chen', 'michael@bookvibe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Technology enthusiast and science fiction lover. I enjoy books that make me think about the future.', '["Sci-Fi", "Non-Fiction", "Thriller"]'),
('Emma Rodriguez', 'emma@bookvibe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Book club organizer and avid reader. I read everything from mysteries to biographies!', '["Mystery", "Biography", "Fiction"]'),
('James Wilson', 'james@bookvibe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'History professor and weekend warrior. Love historical fiction and adventure stories.', '["History", "Adventure", "Fiction"]'),
('Demo User', 'demo@bookvibe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo account for testing BookVibe features. Password: password', '["Fantasy", "Romance"]');

-- ============================================
-- Insert sample books (12 books matching actual image files)
-- ============================================
INSERT INTO books (title, author, isbn, publisher, publication_year, page_count, description, cover_image, genre_id, avg_rating, review_count) VALUES

-- Classic Literature (Fiction - Genre 1) 
('1984', 'George Orwell', '978-0-452-28423-4', 'Plume', 1949, 328, 'A dystopian novel set in a totalitarian society under the omnipresent surveillance of Big Brother.', '1984.jpg', 1, 4.4, 32),

-- The_Great_Gatsby.jpg shows The Great Gatsby cover
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Scribner', 1925, 180, 'A tragic story of Jay Gatsby and his pursuit of the American Dream in the Jazz Age.', 'gatsby.jpg', 1, 4.2, 28),

-- Little_Women.jpg shows Little Women cover
('Little Women', 'Louisa May Alcott', '978-0-14-143965-4', 'Penguin Classics', 1868, 449, 'The story of the four March sisters growing up during the Civil War.', 'little_women.jpg', 1, 4.3, 24),

-- Pride_Predudice.jpg shows Pride and Prejudice cover
('Pride and Prejudice', 'Jane Austen', '978-0-14-143951-7', 'Penguin Classics', 1813, 279, 'A romantic novel about manners and marriage in Georgian England featuring Elizabeth Bennet and Mr. Darcy.', 'Pride_Predudice.jpg', 2, 4.5, 41),

-- The_Shining.jpg shows The Shining cover
('The Shining', 'Stephen King', '978-0-307-74365-9', 'Anchor Books', 1977, 447, 'A psychological horror novel about Jack Torrance, a writer who becomes caretaker of the isolated Overlook Hotel and descends into madness.', 'The_Shining.jpg', 3, 4.7, 52),

-- Harry_Potter.jpg shows Harry Potter cover 
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', '978-0-439-70818-8', 'Scholastic', 1997, 309, 'A young wizard discovers his magical heritage and attends Hogwarts School of Witchcraft and Wizardry.', 'Harry_Potter.jpg', 5, 4.6, 47),

-- Lord_Of_The_Rings.jpg shows Lord of the Rings cover
('The Lord of the Rings', 'J.R.R. Tolkien', '978-0-547-92832-9', 'Mariner Books', 1954, 1216, 'An epic high fantasy novel about the quest to destroy the One Ring and defeat the Dark Lord Sauron in Middle-earth.', 'Lord_Of_The_Rings.jpg', 5, 4.0, 35),

-- Thriller (Genre 3)
('Gone Girl', 'Gillian Flynn', '978-0-307-58836-4', 'Crown Publishers', 2012, 419, 'A psychological thriller about a marriage gone terribly wrong when Amy Dunne disappears.', 'gone_girl.jpg', 3, 4.1, 35),

-- Orient_Express_Agatha.jpg shows Murder on the Orient Express cover:
('Murder on the Orient Express', 'Agatha Christie', '978-0-06-207350-4', 'William Morrow Paperbacks', 1934, 256, 'The story features the Belgian detective Hercule Poirot, who is on a luxury train that becomes stranded by a snowdrift. A murder is committed, and Poirot must solve the baffling mystery before the killer can strike again.', 'Orient_Express_Agatha.jpg', 6, 4.8, 38),

-- Self-Help & Non-Fiction (Genre 7)
('Atomic Habits', 'James Clear', '978-0-7352-1129-2', 'Avery', 2018, 320, 'Tiny changes, remarkable results. An easy way to build good habits and break bad ones.', 'atomic_habits.jpg', 7, 4.6, 43),

-- Lacan_Shakespeare.jpg shows Lacan_Shakespeare.jpg cover:
('From Shakespeare to Camus', 'Sarojakshan Thaikkad', '978-1-4516-4853-9', 'Simon & Schuster', 2011, 656, 'The book explores the relationship between psychoanalysis and literature, specifically examining the works of William Shakespeare and Albert Camus through the lens of Jacques Lacan\s psychoanalytic theories. ', 'Lacan_Shakespeare.jpg', 8, 4.3, 29),

-- Top_Stocks.jpg shows stock market book shows "Top Stocks for Building Wealth" cover:
('Top Stocks for Building Wealth', 'Michael Sincere', '978-0-07-174734-9', 'McGraw-Hill', 2012, 288, 'A comprehensive guide to selecting winning stocks and building long-term wealth in the stock market.', 'Top_Stocks.jpg', 7, 3.8, 15),

-- ============================================
-- NEW BOOKS ADDED - November 8, 2025
-- ============================================
-- Adventure Genre (Genre 10)
('Run', 'Sarah Armstrong', '978-1-2345-6789-0', 'Adventure Press', 2023, 340, 'A gripping survival story that follows a young runner who must navigate dangerous terrain and face life-threatening challenges. This big-hearted adventure explores themes of resilience, determination, and the human spirit''s capacity to endure against all odds.', 'Run.jpg', 10, 4.2, 3),

-- Sci-Fi Genre (Genre 4) 
('Red City', 'Marie Liu', '978-1-2345-6789-1', 'Future Fiction', 2024, 425, 'Set in a dystopian future where cities are color-coded by social hierarchy, this thought-provoking sci-fi novel explores themes of inequality and resistance. When a young woman discovers the truth behind the Red City''s facade, she must choose between safety and revolution.', 'Red_City.jpg', 4, 4.5, 7),

-- Sci-Fi Genre (Genre 4)
('All That We See Or Seem', 'Ken Liu', '978-1-2345-6789-2', 'Speculative Press', 2023, 380, 'A mind-bending exploration of reality and perception that blurs the lines between dreams and waking life. This speculative fiction masterpiece challenges readers to question the nature of existence and consciousness in a world where nothing is quite what it seems.', 'All_That_We_See_Or_Seem.jpg', 4, 4.7, 12);

-- ============================================
-- Triggers to update book ratings and counts
-- ============================================

DELIMITER //

-- Trigger to update book rating when review is added
CREATE TRIGGER update_book_rating_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE books 
    SET avg_rating = (
        SELECT AVG(rating) 
        FROM reviews 
        WHERE book_id = NEW.book_id AND is_public = TRUE
    ),
    review_count = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE book_id = NEW.book_id AND is_public = TRUE
    )
    WHERE book_id = NEW.book_id;
END//

-- Trigger to update book rating when review is updated
CREATE TRIGGER update_book_rating_after_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE books 
    SET avg_rating = (
        SELECT AVG(rating) 
        FROM reviews 
        WHERE book_id = NEW.book_id AND is_public = TRUE
    ),
    review_count = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE book_id = NEW.book_id AND is_public = TRUE
    )
    WHERE book_id = NEW.book_id;
END//

-- Trigger to update book rating when review is deleted
CREATE TRIGGER update_book_rating_after_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE books 
    SET avg_rating = COALESCE((
        SELECT AVG(rating) 
        FROM reviews 
        WHERE book_id = OLD.book_id AND is_public = TRUE
    ), 0),
    review_count = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE book_id = OLD.book_id AND is_public = TRUE
    )
    WHERE book_id = OLD.book_id;
END//

-- Trigger to update genre book count
CREATE TRIGGER update_genre_count_after_insert
AFTER INSERT ON books
FOR EACH ROW
BEGIN
    UPDATE genres 
    SET book_count = book_count + 1 
    WHERE genre_id = NEW.genre_id;
END//

CREATE TRIGGER update_genre_count_after_delete
AFTER DELETE ON books
FOR EACH ROW
BEGIN
    UPDATE genres 
    SET book_count = book_count - 1 
    WHERE genre_id = OLD.genre_id;
END//

DELIMITER ;

-- ============================================
-- Insert sample reviews for the 12 books
-- ============================================
INSERT INTO reviews (user_id, book_id, rating, review_title, review_text, helpful_count) VALUES
-- 1984 reviews (book_id 1)
(1, 1, 4, 'Terrifyingly Relevant', 'Orwell\'s vision feels more relevant today than ever. A masterpiece that everyone should read.', 35),
(4, 1, 5, 'Thought-Provoking', 'A powerful warning about government surveillance and control. The message is crucial.', 29),
(2, 1, 4, 'Dystopian Masterpiece', 'Big Brother is watching. This book will change how you see the world.', 22),

-- The Great Gatsby reviews (book_id 2)
(3, 2, 4, 'American Classic', 'Fitzgerald\'s prose is absolutely beautiful. The American Dream explored perfectly.', 31),
(5, 2, 4, 'Beautifully Written', 'The Jazz Age comes alive. Gatsby is a tragic figure you won\'t forget.', 18),
(1, 2, 4, 'Timeless Themes', 'Wealth, love, and the corruption of the American Dream. Still relevant today.', 24),

-- Little Women reviews (book_id 3)
(2, 3, 4, 'Heartwarming Family Story', 'The March sisters feel like real people. Their struggles and joys are universal.', 26),
(4, 3, 5, 'Comfort Read', 'A lovely story about family, growing up, and finding your place in the world.', 19),
(3, 3, 4, 'Strong Female Characters', 'Each sister is unique and memorable. Alcott was ahead of her time.', 15),

-- Pride and Prejudice reviews (book_id 4)
(4, 4, 5, 'Romance Done Right', 'Austen\'s wit shines through. Elizabeth Bennet is a heroine ahead of her time.', 47),
(1, 4, 5, 'Perfect Romance', 'The slow burn between Elizabeth and Darcy is everything. Sharp social commentary.', 42),
(2, 4, 4, 'Witty and Charming', 'Austen\'s social commentary disguised as romance. Elizabeth is a wonderful character.', 35),

-- The Shining reviews (book_id 5)
(1, 5, 4, 'Masterful Horror', 'King at his best. The psychological descent is terrifying and believable.', 36),
(5, 5, 5, 'Genuinely Scary', 'Couldn\'t sleep after reading. The Overlook Hotel feels like a character itself.', 31),
(2, 5, 4, 'Slow Burn Terror', 'Builds tension perfectly. Jack\'s descent into madness is horrifying.', 24),

-- Harry Potter reviews (book_id 6)
(1, 6, 5, 'Magical Beginning', 'Rowling created a world that feels completely real. Harry\'s journey from cupboard to Hogwarts is captivating.', 52),
(2, 6, 5, 'Perfect for All Ages', 'Adults and children alike will be enchanted. The magic system is brilliantly thought out.', 41),
(3, 6, 4, 'Great Start to Series', 'Sets up the world perfectly. Some pacing issues but the magic makes up for it.', 28),

-- Lord of the Rings reviews (book_id 7)
(3, 7, 5, 'Epic Fantasy Masterpiece', 'Tolkien created the ultimate fantasy world. The depth and detail is unmatched.', 45),
(2, 7, 4, 'Rich World Building', 'Middle-earth feels completely real. The journey is long but worth every page.', 38),
(4, 7, 5, 'Timeless Adventure', 'A classic that inspired all fantasy that came after. Characters you\'ll never forget.', 32),

-- Gone Girl reviews (book_id 8)
(3, 8, 4, 'Twisted Psychological Thriller', 'Flynn keeps you guessing until the very end. The unreliable narrators are masterful.', 32),
(2, 8, 4, 'Dark Page Turner', 'Couldn\'t put it down! The plot twists are shocking. Not for the faint of heart.', 28),
(4, 8, 4, 'Complex Characters', 'Both Nick and Amy are fascinating and terrible. Makes you question everything.', 19),

-- Murder on the Orient Express reviews (book_id 9)
(3, 9, 5, 'Classic Mystery', 'Christie at her finest. The plot twists are brilliant and the solution unexpected.', 34),
(4, 9, 4, 'Poirot Perfection', 'Hercule Poirot is the perfect detective. The setting on the train is atmospheric.', 28),
(1, 9, 4, 'Clever Puzzle', 'A masterclass in mystery writing. Every clue matters in this ingenious plot.', 22),

-- Atomic Habits reviews (book_id 10)
(2, 10, 5, 'Life Changing', 'Clear\'s approach is practical and actually works. Built 3 new habits using his methods.', 45),
(4, 10, 5, 'Best Self-Help Book', 'Finally a self-help book that\'s actionable. The 1% improvement concept is genius.', 38),
(1, 10, 4, 'Practical and Useful', 'Easy to understand and implement. Actually saw results within weeks.', 33),

-- From Shakespeare to Camus reviews (book_id 11)
(2, 11, 4, 'Intellectual Journey', 'Thaikkad bridges literature and psychoanalysis beautifully. Complex but rewarding.', 18),
(4, 11, 4, 'Scholarly Excellence', 'Deep analysis of how psychoanalytic theory illuminates classic literature.', 15),
(1, 11, 3, 'Academic but Accessible', 'Heavy material but well-presented. Good for literature students.', 12),

-- Top Stocks for Building Wealth reviews (book_id 12)
(5, 12, 4, 'Good Investment Primer', 'Solid advice for beginners. The stock selection criteria are helpful.', 12),
(3, 12, 4, 'Practical Investing Guide', 'Easy to understand approach to stock picking. Good for new investors.', 8),
(1, 12, 3, 'Basic but Useful', 'Covers the fundamentals well. Could use more advanced strategies.', 6);

-- ============================================
-- Insert sample favorites for our 12 books 
-- ============================================
INSERT INTO favorites (user_id, book_id) VALUES
(1, 1), (1, 2), (1, 5), (1, 10),
(2, 1), (2, 4), (2, 6), (2, 8), (2, 12),
(3, 3), (3, 5), (3, 7), (3, 9),
(4, 2), (4, 6), (4, 7), (4, 10), (4, 12),
(5, 1), (5, 3), (5, 6), (5, 9), (5, 12);



-- Update book ratings based on reviews (in case triggers didn't fire)
UPDATE books b 
SET avg_rating = COALESCE((
    SELECT AVG(r.rating) 
    FROM reviews r 
    WHERE r.book_id = b.book_id AND r.is_public = TRUE
), 0),
review_count = (
    SELECT COUNT(*) 
    FROM reviews r 
    WHERE r.book_id = b.book_id AND r.is_public = TRUE
);

-- Update genre book counts
UPDATE genres g 
SET book_count = (
    SELECT COUNT(*) 
    FROM books b 
    WHERE b.genre_id = g.genre_id
);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Create indexes for better performance
-- ============================================
CREATE INDEX idx_books_search ON books(title, author);
CREATE INDEX idx_reviews_recent ON reviews(created_at DESC);
CREATE INDEX idx_favorites_user_recent ON favorites(user_id, added_at DESC);

-- Success message
SELECT 'BookVibe database created successfully!' as Status,
       COUNT(*) as total_books FROM books;

SELECT 'Sample data inserted!' as Status,
       (SELECT COUNT(*) FROM users) as users,
       (SELECT COUNT(*) FROM books) as books,
       (SELECT COUNT(*) FROM reviews) as reviews,
       (SELECT COUNT(*) FROM favorites) as favorites;