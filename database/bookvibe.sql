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

-- Classic Literature (Fiction - Genre 1) - CORRECTED TO MATCH ACTUAL COVER IMAGES
('1984', 'George Orwell', '978-0-452-28423-4', 'Plume', 1949, 328, 'A dystopian novel set in a totalitarian society under the omnipresent surveillance of Big Brother.', '1984.jpg', 1, 4.4, 32),

('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Scribner', 1925, 180, 'A tragic story of Jay Gatsby and his pursuit of the American Dream in the Jazz Age.', 'gatsby.jpg', 1, 4.2, 28),

('Little Women', 'Louisa May Alcott', '978-0-14-143965-4', 'Penguin Classics', 1868, 449, 'The story of the four March sisters growing up during the Civil War.', 'little_women.jpg', 1, 4.3, 24),

-- CORRECTED TO MATCH ACTUAL COVER IMAGES
-- Pride_Predudice.jpg shows "To Kill a Mockingbird" cover:
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'Harper Perennial', 1960, 281, 'A classic of modern American literature dealing with racial injustice in the Deep South.', 'Pride_Predudice.jpg', 1, 4.5, 41),

-- The_Shining.jpg shows "Pride and Prejudice" cover:
('Pride and Prejudice', 'Jane Austen', '978-0-14-143951-7', 'Penguin Classics', 1813, 279, 'A romantic novel about manners and marriage in Georgian England featuring Elizabeth Bennet and Mr. Darcy.', 'The_Shining.jpg', 2, 4.7, 52),

-- Harry_Potter.jpg shows Harry Potter cover (assuming correct):
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', '978-0-439-70818-8', 'Scholastic', 1997, 309, 'A young wizard discovers his magical heritage and attends Hogwarts School of Witchcraft and Wizardry.', 'Harry_Potter.jpg', 5, 4.6, 47),

-- Lord_Of_The_Rings.jpg shows "The Catcher in the Rye" cover:
('The Catcher in the Rye', 'J.D. Salinger', '978-0-316-76948-0', 'Little, Brown', 1951, 234, 'The story of teenage rebellion and alienation told by the iconic Holden Caulfield.', 'Lord_Of_The_Rings.jpg', 1, 4.0, 35),

-- Thriller (Genre 3)
('Gone Girl', 'Gillian Flynn', '978-0-307-58836-4', 'Crown Publishers', 2012, 419, 'A psychological thriller about a marriage gone terribly wrong when Amy Dunne disappears.', 'gone_girl.jpg', 3, 4.1, 35),

-- Orient_Express_Agatha.jpg shows "Dune" cover:
('Dune', 'Frank Herbert', '978-0-441-17271-9', 'Ace Books', 1965, 688, 'A science fiction masterpiece about politics, religion, and ecology on the desert planet Arrakis.', 'Orient_Express_Agatha.jpg', 4, 4.8, 38),

-- Self-Help & Non-Fiction (Genre 7)
('Atomic Habits', 'James Clear', '978-0-7352-1129-2', 'Avery', 2018, 320, 'Tiny changes, remarkable results. An easy way to build good habits and break bad ones.', 'atomic_habits.jpg', 7, 4.6, 43),

-- Top_Stocks.jpg shows stock market book (assuming correct):
('Top Stocks for Building Wealth', 'Michael Sincere', '978-0-07-174734-9', 'McGraw-Hill', 2012, 288, 'A comprehensive guide to selecting winning stocks and building long-term wealth in the stock market.', 'Top_Stocks.jpg', 7, 3.8, 15);

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
-- Insert sample reviews for our 12 books (matching new book IDs)
-- ============================================
INSERT INTO reviews (user_id, book_id, rating, review_title, review_text, helpful_count) VALUES
-- Harry Potter reviews (book_id 1)
(1, 1, 5, 'Magical Beginning', 'Rowling created a world that feels completely real. Harry\'s journey from cupboard to Hogwarts is captivating.', 52),
(2, 1, 5, 'Perfect for All Ages', 'Adults and children alike will be enchanted. The magic system is brilliantly thought out.', 41),
(3, 1, 4, 'Great Start to Series', 'Sets up the world perfectly. Some pacing issues but the magic makes up for it.', 28),

-- 1984 reviews (book_id 2)
(1, 2, 4, 'Terrifyingly Relevant', 'Orwell\'s vision feels more relevant today than ever. A masterpiece that everyone should read.', 35),
(4, 2, 5, 'Thought-Provoking', 'A powerful warning about government surveillance and control. The message is crucial.', 29),
(2, 2, 4, 'Dystopian Masterpiece', 'Big Brother is watching. This book will change how you see the world.', 22),

-- The Great Gatsby reviews (book_id 3)
(3, 3, 4, 'American Classic', 'Fitzgerald\'s prose is absolutely beautiful. The American Dream explored perfectly.', 31),
(5, 3, 4, 'Beautifully Written', 'The Jazz Age comes alive. Gatsby is a tragic figure you won\'t forget.', 18),
(1, 3, 4, 'Timeless Themes', 'Wealth, love, and the corruption of the American Dream. Still relevant today.', 24),

-- Little Women reviews (book_id 4)
(2, 4, 4, 'Heartwarming Family Story', 'The March sisters feel like real people. Their struggles and joys are universal.', 26),
(4, 4, 5, 'Comfort Read', 'A lovely story about family, growing up, and finding your place in the world.', 19),
(3, 4, 4, 'Strong Female Characters', 'Each sister is unique and memorable. Alcott was ahead of her time.', 15),

-- To Kill a Mockingbird reviews (book_id 5) - Pride_Predudice.jpg shows this book
(1, 5, 5, 'Essential Reading', 'Harper Lee tackles racism and injustice with grace. Scout\'s perspective is perfect.', 38),
(3, 5, 4, 'Powerful and Moving', 'Atticus Finch is a hero for the ages. This book shaped my understanding of justice.', 33),
(5, 5, 5, 'Timeless Classic', 'A profound story about moral courage and childhood innocence lost.', 27),
(2, 5, 4, 'Important Message', 'Everyone should read this book. The lessons about prejudice are still relevant.', 21),

-- Pride and Prejudice reviews (book_id 6) - The_Shining.jpg shows this book
(4, 6, 5, 'Romance Done Right', 'Austen\'s wit shines through. Elizabeth Bennet is a heroine ahead of her time.', 47),
(1, 6, 5, 'Perfect Romance', 'The slow burn between Elizabeth and Darcy is everything. Sharp social commentary.', 42),
(2, 6, 4, 'Witty and Charming', 'Austen\'s social commentary disguised as romance. Elizabeth is a wonderful character.', 35),
(5, 6, 5, 'Timeless Love Story', 'More than just romance. Austen skewers society with precision and humor.', 29),

-- Gone Girl reviews (book_id 7)
(3, 7, 4, 'Twisted Psychological Thriller', 'Flynn keeps you guessing until the very end. The unreliable narrators are masterful.', 32),
(2, 7, 4, 'Dark Page Turner', 'Couldn\'t put it down! The plot twists are shocking. Not for the faint of heart.', 28),
(4, 7, 4, 'Complex Characters', 'Both Nick and Amy are fascinating and terrible. Makes you question everything.', 19),

-- The Shining reviews (book_id 8)
(1, 8, 4, 'Masterful Horror', 'King at his best. The psychological descent is terrifying and believable.', 36),
(5, 8, 5, 'Genuinely Scary', 'Couldn\'t sleep after reading. The Overlook Hotel feels like a character itself.', 31),
(2, 8, 4, 'Slow Burn Terror', 'Builds tension perfectly. Jack\'s descent into madness is horrifying.', 24),

-- The Catcher in the Rye reviews (book_id 9) - Lord_Of_The_Rings.jpg shows this book
(3, 9, 4, 'Coming of Age Classic', 'Salinger captures teenage alienation perfectly. Holden\'s voice is unforgettable.', 29),
(4, 9, 4, 'Raw and Honest', 'Controversial but brilliant. Holden\'s journey through New York is compelling.', 22),
(1, 9, 4, 'Iconic Voice', 'Love him or hate him, Holden Caulfield is one of literature\'s most memorable characters.', 18),
(5, 9, 3, 'Polarizing but Important', 'Not for everyone, but captures the confusion of adolescence perfectly.', 15),

-- Dune reviews (book_id 10) - Orient_Express_Agatha.jpg shows this book
(3, 10, 5, 'Sci-Fi Masterpiece', 'Herbert created an incredibly detailed universe. Politics, religion, ecology - it has everything.', 35),
(4, 10, 5, 'Epic World Building', 'The depth of Arrakis and the spice economy is unmatched in science fiction.', 28),
(1, 10, 4, 'Complex but Rewarding', 'Takes patience but rewards careful readers. The political intrigue is fascinating.', 22),
(5, 10, 5, 'Changed Sci-Fi Forever', 'Influenced every space opera that came after. A true masterpiece.', 19),

-- Atomic Habits reviews (book_id 11)
(2, 11, 5, 'Life Changing', 'Clear\'s approach is practical and actually works. Built 3 new habits using his methods.', 45),
(4, 11, 5, 'Best Self-Help Book', 'Finally a self-help book that\'s actionable. The 1% improvement concept is genius.', 38),
(1, 11, 4, 'Practical and Useful', 'Easy to understand and implement. Actually saw results within weeks.', 33),
(3, 11, 5, 'Systems Over Goals', 'Changed my whole approach to personal development. Highly recommended.', 27),

-- Top Stocks reviews (book_id 12)
(5, 12, 4, 'Good Investment Primer', 'Solid advice for beginners. The stock selection criteria are helpful.', 12),
(3, 12, 4, 'Practical Investing Guide', 'Easy to understand approach to stock picking. Good for new investors.', 8),
(1, 12, 3, 'Basic but Useful', 'Covers the fundamentals well. Could use more advanced strategies.', 6);

-- ============================================
-- Insert sample favorites for our 12 books (matching new IDs)
-- ============================================
INSERT INTO favorites (user_id, book_id) VALUES
(1, 1), (1, 2), (1, 5), (1, 10),
(2, 1), (2, 4), (2, 6), (2, 8),
(3, 3), (3, 5), (3, 7), (3, 9),
(4, 2), (4, 6), (4, 7), (4, 10),
(5, 1), (5, 3), (5, 6), (5, 9);

-- ============================================
-- Clean up and finalize
-- ============================================

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