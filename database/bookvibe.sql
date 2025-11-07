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
-- Insert sample books (12 books matching our images)
-- ============================================
INSERT INTO books (title, author, isbn, publisher, publication_year, page_count, description, cover_image, genre_id, price, sale_price) VALUES

-- Classic Literature (Fiction)
('1984', 'George Orwell', '978-0-452-28423-4', 'Plume', 1949, 328, 'A dystopian novel set in a totalitarian society under the omnipresent surveillance of Big Brother.', '1984.jpg', 1, 14.99, 12.99),

('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Scribner', 1925, 180, 'A tragic story of Jay Gatsby and his pursuit of the American Dream in the Jazz Age.', 'gatsby.jpg', 1, 13.99, 11.99),

('Little Women', 'Louisa May Alcott', '978-0-14-143965-4', 'Penguin Classics', 1868, 449, 'The story of the four March sisters growing up during the Civil War.', 'little_women.jpg', 1, 12.99, 10.99),

-- Contemporary Fiction & Thriller
('Gone Girl', 'Gillian Flynn', '978-0-307-58836-4', 'Crown Publishers', 2012, 419, 'A psychological thriller about a marriage gone terribly wrong.', 'gone_girl.jpg', 3, 16.99, 14.99),

-- Self-Help & Non-Fiction  
('Atomic Habits', 'James Clear', '978-0-7352-1129-2', 'Avery', 2018, 320, 'Tiny changes, remarkable results. An easy way to build good habits and break bad ones.', 'atomic_habits.jpg', 7, 18.99, 16.99),

-- Additional Books
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'Harper Perennial', 1960, 281, 'A classic of modern American literature dealing with racial injustice in the Deep South.', 'google_s1gVAAAAYAAJ.jpg', 1, 14.99, 12.99),

('Pride and Prejudice', 'Jane Austen', '978-0-14-143951-7', 'Penguin Classics', 1813, 279, 'A romantic novel about manners and marriage in Georgian England.', 'google_QABREQAAQBAJ.jpg', 2, 13.99, 11.99),

('The Catcher in the Rye', 'J.D. Salinger', '978-0-316-76948-0', 'Little, Brown', 1951, 234, 'The story of teenage rebellion and alienation.', 'google_GWorEAAAQBAJ.jpg', 1, 15.99, 13.99),

-- Mystery & Crime
('The Girl with the Dragon Tattoo', 'Stieg Larsson', '978-0-307-47347-9', 'Vintage Crime', 2005, 590, 'A gripping mystery combining murder, family saga, and financial corruption.', 'google_YL_aEAAAQBAJ.jpg', 6, 17.99, 15.99),

-- Science Fiction
('Dune', 'Frank Herbert', '978-0-441-17271-9', 'Ace Books', 1965, 688, 'A science fiction masterpiece about politics, religion, and ecology on the desert planet Arrakis.', 'google_bXp2EQAAQBAJ.jpg', 4, 19.99, 17.99),

-- Biography  
('Steve Jobs', 'Walter Isaacson', '978-1-4516-4853-9', 'Simon & Schuster', 2011, 656, 'The exclusive biography of the Apple co-founder.', 'google_iICQDwAAQBAJ.jpg', 8, 22.99, 19.99),

-- Non-Fiction/History
('Sapiens', 'Yuval Noah Harari', '978-0-06-231609-7', 'Harper', 2015, 443, 'A brief history of humankind from the Stone Age to the 21st century.', 'google_mSwvswEACAAJ.jpg', 9, 21.99, 18.99);

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
-- Insert sample reviews for our 12 books
-- ============================================
INSERT INTO reviews (user_id, book_id, rating, review_title, review_text, helpful_count) VALUES
-- 1984 reviews
(1, 1, 5, 'Terrifyingly Relevant', 'Orwell\'s vision of totalitarian control feels more relevant today than ever. A masterpiece that everyone should read.', 24),
(2, 1, 4, 'Thought-Provoking', 'A powerful warning about government surveillance and control. Some parts are slow but the message is important.', 18),

-- The Great Gatsby reviews
(3, 2, 5, 'American Classic', 'Fitzgerald\'s prose is absolutely beautiful. The story of the American Dream gone wrong still resonates today.', 31),
(4, 2, 4, 'Beautifully Written', 'The Jazz Age comes alive in this novel. Gatsby is a tragic figure you won\'t forget.', 15),

-- Little Women reviews  
(1, 3, 5, 'Timeless Story', 'The March sisters feel like real people. Their struggles and joys are universal. A comfort read I return to often.', 22),
(5, 3, 4, 'Heartwarming', 'A lovely story about family, growing up, and finding your place in the world.', 12),

-- Gone Girl reviews
(2, 4, 5, 'Twisted and Brilliant', 'Flynn keeps you guessing until the very end. The unreliable narrators are masterfully done.', 28),
(3, 4, 4, 'Page Turner', 'Couldn\'t put it down! The plot twists are shocking. Not for the faint of heart.', 19),

-- Atomic Habits reviews
(4, 5, 5, 'Life Changing', 'Clear\'s approach is practical and actually works. I\'ve successfully built 3 new habits using his methods.', 45),
(1, 5, 5, 'Best Self-Help Book', 'Finally a self-help book that\'s actionable. The 1% improvement concept is genius.', 33),

-- To Kill a Mockingbird reviews
(5, 6, 5, 'Essential Reading', 'Harper Lee tackles racism and injustice with grace. Scout\'s perspective is perfect.', 27),
(2, 6, 5, 'Powerful and Moving', 'Atticus Finch is a hero for the ages. This book shaped my understanding of justice.', 21),

-- Pride and Prejudice reviews
(3, 7, 4, 'Romance Done Right', 'Austen\'s wit shines through. Elizabeth Bennet is a heroine ahead of her time.', 16),
(4, 7, 5, 'Perfect Romance', 'The slow burn between Elizabeth and Darcy is everything. Austen\'s social commentary is sharp.', 24),

-- Steve Jobs reviews
(1, 11, 4, 'Fascinating Portrait', 'Isaacson doesn\'t pull punches. Shows both the genius and the difficult personality.', 38),
(5, 11, 5, 'Comprehensive Biography', 'The definitive biography of a complex man who changed the world.', 30);

-- ============================================
-- Insert sample favorites for our 12 books
-- ============================================
INSERT INTO favorites (user_id, book_id) VALUES
(1, 1), (1, 3), (1, 5), (1, 11),
(2, 1), (2, 4), (2, 6), (2, 10),
(3, 2), (3, 4), (3, 7), (3, 12),
(4, 5), (4, 7), (4, 9), (4, 11),
(5, 3), (5, 6), (5, 8), (5, 11);

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