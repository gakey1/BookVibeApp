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
-- Insert 50+ sample books with complete metadata
-- ============================================
INSERT INTO books (title, author, isbn, publisher, publication_year, page_count, description, cover_image, genre_id, price, sale_price) VALUES

-- Fantasy Books
('The Enchanted Forest', 'Luna Blackwood', '978-1234567890', 'Fantasy Press', 2024, 384, 'In the mystical realm of Eldoria, where ancient magic flows through every leaf and stone, young Aria discovers she possesses a rare gift that could either save her world or destroy it forever. When dark forces threaten to consume the Enchanted Forest, she must embark on a perilous journey to unlock the secrets of her heritage.', 'enchanted_forest.jpg', 5, 24.99, 19.99),

('Dragon Quest: Legacy of Fire', 'Marcus Dragonbane', '978-1234567891', 'Epic Tales Publishing', 2024, 456, 'The last dragon rider must unite the fractured kingdoms before an ancient evil awakens. With his dragon companion Emberwing, Kael embarks on a quest that will determine the fate of all magical creatures.', 'dragon_quest.jpg', 5, 27.99, 22.99),

('The Crystal Chronicles', 'Seraphina Vale', '978-1234567892', 'Mystic Works', 2023, 398, 'When the sacred crystals that protect the realm begin to shatter, a young mage must master forbidden magic to prevent the world from falling into eternal darkness.', 'crystal_chronicles.jpg', 5, 23.99, 18.99),

('Realm of Shadows', 'Darius Nightfall', '978-1234567893', 'Dark Fantasy Ltd', 2024, 425, 'In a world where shadows have gained consciousness, a shadow-walker must choose between saving humanity or embracing the darkness that calls to her.', 'realm_shadows.jpg', 5, 26.99, 21.99),

('The Elemental Wars', 'Aria Stormwind', '978-1234567894', 'Elemental Press', 2023, 367, 'Four elemental kingdoms stand on the brink of war. Only a young woman who can control all four elements can restore balance to the world.', 'elemental_wars.jpg', 5, 25.99, 20.99),

-- Romance Books
('Love in Paris', 'Emma Rose', '978-2234567890', 'Romance Central', 2023, 342, 'When art curator Sophie inherits a mysterious painting in Paris, she discovers it holds clues to a century-old love story. As she unravels the mystery, she finds herself falling for the charming antiquarian helping her search.', 'love_paris.jpg', 2, 19.99, 15.99),

('Heart of Stone', 'Rebecca Time', '978-2234567891', 'Sweet Romance', 2023, 298, 'A successful businesswoman returns to her hometown and reconnects with her high school sweetheart, now a single father. Can they overcome past hurts to find love again?', 'heart_stone.jpg', 2, 18.99, 14.99),

('Summer Nights', 'Isabella Moon', '978-2234567892', 'Sunset Publishing', 2024, 315, 'A beach house, a mysterious neighbor, and the summer that changes everything. When city girl meets small-town charm, sparks fly under the starlit sky.', 'summer_nights.jpg', 2, 20.99, 16.99),

('The Wedding Planner''s Heart', 'Sophia Grace', '978-2234567893', 'Ever After Books', 2024, 289, 'Wedding planner Charlotte has planned hundreds of perfect weddings but never found her own happily ever after. That changes when she meets the cynical divorce lawyer who challenges everything she believes about love.', 'wedding_planner.jpg', 2, 19.99, 15.99),

('Whispers of the Heart', 'Melody Rivers', '978-2234567894', 'Heartstring Publications', 2023, 334, 'A deaf musician and a sound engineer find love through the universal language of music in this touching contemporary romance.', 'whispers_heart.jpg', 2, 21.99, 17.99),

-- Thriller Books
('Midnight Shadows', 'Robert Kane', '978-3234567890', 'Thriller House', 2024, 412, 'Detective Sarah Blake thought she had seen the worst of humanity until a serial killer starts leaving cryptic messages that seem to know her darkest secrets. A thrilling ride from start to finish with plot twists that will keep you guessing.', 'midnight_shadows.jpg', 3, 22.99, 18.99),

('The Silent Witness', 'Victoria Cross', '978-3234567891', 'Crime & Mystery Co', 2024, 387, 'When the only witness to a murder refuses to speak, investigator Matt Torres must uncover the truth before the killer strikes again.', 'silent_witness.jpg', 3, 24.99, 19.99),

('Blood Moon Rising', 'Hunter Black', '978-3234567892', 'Dark Thrillers', 2023, 445, 'A small town harbors a deadly secret that surfaces every decade under the blood moon. FBI agent Lisa Monroe has 72 hours to stop the killing spree.', 'blood_moon.jpg', 3, 23.99, 18.99),

('The Vanishing Hour', 'Rachel Storm', '978-3234567893', 'Suspense Central', 2024, 356, 'People are disappearing at exactly 3:33 AM. A insomniac journalist becomes obsessed with solving the mystery before she becomes the next victim.', 'vanishing_hour.jpg', 3, 21.99, 17.99),

('Web of Lies', 'David Sharp', '978-3234567894', 'Conspiracy Press', 2023, 398, 'A web developer discovers a conspiracy hidden in the code of a popular social media platform. Now corporate assassins are hunting him down.', 'web_lies.jpg', 3, 25.99, 20.99),

-- Sci-Fi Books
('Quantum Reality', 'Dr. Alan Chen', '978-4234567890', 'Future Tech Publishing', 2024, 467, 'When quantum physicist Dr. Elena Vasquez discovers that parallel universes are bleeding into our reality, she must race against time to prevent a catastrophic collision of worlds that could erase existence itself.', 'quantum_reality.jpg', 4, 28.99, 23.99),

('Digital Dreams', 'Marcus Tech', '978-4234567891', 'Cyber Fiction Inc', 2024, 423, 'In 2087, consciousness can be uploaded to the cloud. But when the first digital human starts questioning reality, the line between artificial and authentic becomes dangerously blurred.', 'digital_dreams.jpg', 4, 26.99, 21.99),

('Stars Beyond Tomorrow', 'Captain Nova', '978-4234567892', 'Space Adventures', 2023, 389, 'Humanity''s first interstellar colony ship encounters an alien artifact that challenges everything we know about the universe.', 'stars_tomorrow.jpg', 4, 27.99, 22.99),

('The Android''s Dream', 'Cyber Smith', '978-4234567893', 'Robot Fiction', 2024, 356, 'An android gains consciousness and struggles with what it means to be human in a world that sees it only as a machine.', 'android_dream.jpg', 4, 24.99, 19.99),

('Time Fracture', 'Temporal Jones', '978-4234567894', 'Time Travel Tales', 2023, 412, 'A time traveler gets stuck in a loop where every change he makes creates worse outcomes for humanity.', 'time_fracture.jpg', 4, 25.99, 20.99),

-- Mystery Books
('The Last Detective', 'Sarah Mitchell', '978-5234567890', 'Mystery Manor', 2023, 345, 'Retired detective John Harper thought his case-solving days were over until his neighbor is found dead under impossible circumstances. With the local police stumped, he must use all his experience to solve one final case.', 'last_detective.jpg', 6, 20.99, 16.99),

('Secrets of the Lighthouse', 'Coastal Mystery', '978-5234567891', 'Seaside Mysteries', 2024, 312, 'When a lighthouse keeper disappears during a storm, the only clues are a cryptic message and a room that should not exist.', 'lighthouse_secrets.jpg', 6, 19.99, 15.99),

('The Locked Room', 'Puzzle Master', '978-5234567892', 'Classic Mystery', 2023, 298, 'A impossible murder in a locked room brings together an unlikely detective duo in this homage to classic mystery novels.', 'locked_room.jpg', 6, 18.99, 14.99),

('Murder at Midnight Manor', 'Gothic Writer', '978-5234567893', 'Manor House Books', 2024, 367, 'A weekend gathering at a remote manor turns deadly when guests start disappearing one by one during a thunderstorm.', 'midnight_manor.jpg', 6, 22.99, 18.99),

('The Cipher Killer', 'Code Breaker', '978-5234567894', 'Cryptic Tales', 2023, 389, 'A serial killer leaves coded messages at crime scenes. A cryptographer races to decode them before the next murder.', 'cipher_killer.jpg', 6, 21.99, 17.99),

-- Non-Fiction Books
('Ancient Wisdom', 'Prof. History', '978-6234567890', 'Academic Press', 2023, 456, 'A comprehensive exploration of ancient civilizations and the timeless wisdom they offer modern society. From the philosophical insights of ancient Greece to the technological marvels of lost civilizations.', 'ancient_wisdom.jpg', 7, 32.99, 27.99),

('The Science of Happiness', 'Dr. Wellness', '978-6234567891', 'Self-Help Central', 2024, 298, 'Backed by research, this book reveals the science behind happiness and provides practical strategies for a more fulfilling life.', 'science_happiness.jpg', 7, 24.99, 19.99),

('Digital Minimalism', 'Tech Philosopher', '978-6234567892', 'Modern Life Press', 2023, 267, 'How to reclaim your life from technology addiction and find meaning in a hyperconnected world.', 'digital_minimalism.jpg', 7, 23.99, 18.99),

('The Art of Decision Making', 'Choice Expert', '978-6234567893', 'Business Wisdom', 2024, 334, 'Master the psychology and strategy behind making better decisions in all areas of life.', 'art_decisions.jpg', 7, 26.99, 21.99),

('Climate Change: A Visual Guide', 'Environmental Scientist', '978-6234567894', 'Earth Sciences', 2023, 245, 'An accessible guide to understanding climate change through compelling visuals and clear explanations.', 'climate_guide.jpg', 7, 29.99, 24.99),

-- Biography Books
('Life of Einstein', 'Bio Writer', '978-7234567890', 'Biography House', 2023, 512, 'The definitive biography of Albert Einstein, exploring not just his scientific genius but his personal struggles, political views, and the man behind the theory of relativity.', 'einstein_life.jpg', 8, 34.99, 29.99),

('The Inventor''s Mind', 'Innovation Author', '978-7234567891', 'Pioneer Press', 2024, 387, 'The fascinating story of Nikola Tesla and his revolutionary inventions that changed the world.', 'inventor_mind.jpg', 8, 31.99, 26.99),

('Queen of Code', 'Tech Historian', '978-7234567892', 'Women in Tech', 2023, 298, 'The inspiring biography of Ada Lovelace, the world''s first computer programmer.', 'queen_code.jpg', 8, 27.99, 22.99),

('The Artist''s Journey', 'Art Biographer', '978-7234567893', 'Creative Lives', 2024, 356, 'The tumultuous and inspiring life of Vincent van Gogh told through his letters and paintings.', 'artist_journey.jpg', 8, 30.99, 25.99),

('Space Pioneer', 'Space Writer', '978-7234567894', 'Cosmic Books', 2023, 445, 'The remarkable life of Katherine Johnson, the mathematician whose calculations helped put humans on the moon.', 'space_pioneer.jpg', 8, 28.99, 23.99),

-- History Books
('The Great Wars', 'Military Historian', '978-8234567890', 'Historical Press', 2023, 567, 'A comprehensive look at the major conflicts that shaped the 20th century, from causes to consequences.', 'great_wars.jpg', 9, 39.99, 34.99),

('Ancient Civilizations', 'Archaeology Expert', '978-8234567891', 'Ancient World', 2024, 423, 'Explore the rise and fall of great civilizations from Mesopotamia to the Maya.', 'ancient_civs.jpg', 9, 35.99, 30.99),

('The Renaissance Revolution', 'Renaissance Scholar', '978-8234567892', 'Cultural History', 2023, 398, 'How the Renaissance changed art, science, and human thought forever.', 'renaissance_rev.jpg', 9, 33.99, 28.99),

('Maritime Empires', 'Naval Historian', '978-8234567893', 'Ocean Chronicles', 2024, 456, 'The age of exploration and how maritime empires shaped the modern world.', 'maritime_empires.jpg', 9, 36.99, 31.99),

('The Cold War Chronicles', 'Modern Historian', '978-8234567894', 'Political History', 2023, 489, 'A detailed examination of the Cold War era and its lasting impact on global politics.', 'cold_war.jpg', 9, 37.99, 32.99),

-- Adventure Books
('Ocean Mysteries', 'Captain Blue', '978-9234567890', 'Adventure Press', 2024, 398, 'Marine biologist Dr. Sarah Ocean leads an expedition to explore the deepest trenches of the Pacific, where they discover creatures that should not exist and secrets that threaten to change our understanding of life on Earth.', 'ocean_mysteries.jpg', 10, 25.99, 20.99),

('Mountain Peak', 'Summit Seeker', '978-9234567891', 'Extreme Adventures', 2023, 345, 'A thrilling account of climbing the world''s most dangerous peak, where survival depends on more than just skill.', 'mountain_peak.jpg', 10, 24.99, 19.99),

('Jungle Expedition', 'Explorer Jones', '978-9234567892', 'Wild Adventures', 2024, 367, 'Lost in the Amazon rainforest, a research team must survive against all odds while uncovering an ancient secret.', 'jungle_expedition.jpg', 10, 23.99, 18.99),

('Desert Crossing', 'Sand Walker', '978-9234567893', 'Survival Stories', 2023, 289, 'A harrowing journey across the Sahara Desert becomes a test of human endurance and determination.', 'desert_crossing.jpg', 10, 22.99, 17.99),

('Arctic Quest', 'Ice Explorer', '978-9234567894', 'Polar Adventures', 2024, 412, 'A race against time in the Arctic wilderness to prevent an environmental disaster.', 'arctic_quest.jpg', 10, 26.99, 21.99),

-- Fiction Books
('The Memory Keeper', 'Literary Master', '978-0234567890', 'Literary Fiction', 2024, 423, 'A haunting tale of family secrets spanning three generations, where memories hold the key to redemption.', 'memory_keeper.jpg', 1, 24.99, 19.99),

('Small Town Secrets', 'Hometown Writer', '978-0234567891', 'American Stories', 2023, 356, 'When a journalist returns to her hometown to investigate a decades-old disappearance, she uncovers secrets that threaten the entire community.', 'small_town.jpg', 1, 21.99, 16.99),

('The Last Library', 'Book Lover', '978-0234567892', 'Literary Tales', 2024, 398, 'In a world where books are banned, a librarian becomes part of an underground network to preserve human knowledge.', 'last_library.jpg', 1, 23.99, 18.99),

('River''s End', 'Nature Writer', '978-0234567893', 'River Valley Press', 2023, 312, 'A family legacy unfolds along the banks of a river that holds both beauty and tragedy.', 'rivers_end.jpg', 1, 20.99, 15.99),

('The Photographer''s Eye', 'Visual Storyteller', '978-0234567894', 'Image & Word', 2024, 367, 'A war photographer returns home to find that the most challenging pictures are the ones closest to heart.', 'photographer_eye.jpg', 1, 25.99, 20.99);

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
-- Insert sample reviews
-- ============================================
INSERT INTO reviews (user_id, book_id, rating, review_title, review_text, helpful_count) VALUES
(1, 1, 5, 'Absolutely Magical!', 'This book completely swept me away! The world-building is incredible and the characters are so well-developed. Aria''s journey is both heart-wrenching and inspiring. The magic system is unique and well-thought-out. I couldn''t put it down and read it in one sitting. Can''t wait for the sequel!', 24),
(2, 1, 4, 'Great Fantasy Adventure', 'Really enjoyed this book! The pacing is excellent and the plot twists kept me guessing. The only reason I''m giving 4 stars instead of 5 is that some of the side characters could have been more developed. But overall, it''s a fantastic read that I''d recommend to any fantasy fan.', 18),
(3, 1, 5, 'Couldn''t Put It Down!', 'What an amazing debut! The author has created such a rich and immersive world. The descriptions are vivid without being overwhelming, and the dialogue feels natural. The relationship between Aria and her companions is beautifully written. This is going straight to my favorites shelf!', 31),

(1, 11, 4, 'Thrilling Ride', 'A suspenseful thriller that kept me on the edge of my seat. Detective Blake is a well-developed character and the mystery is genuinely puzzling.', 15),
(2, 11, 5, 'Couldn''t Stop Reading', 'This book had me hooked from the first chapter. The twists and turns were unexpected and the ending was satisfying.', 22),

(3, 6, 5, 'Beautiful Romance', 'Such a beautiful romance! The Parisian setting was so vivid, I felt like I was there. The chemistry between the characters was amazing and the writing was just gorgeous.', 28),
(4, 6, 4, 'Charming Story', 'A delightful romance with well-developed characters. The art world setting added a nice touch to the love story.', 12),

(1, 16, 5, 'Mind-Bending Sci-Fi', 'Dr. Chen has created a fascinating exploration of quantum physics and parallel universes. The science is well-researched and the story is gripping.', 33),
(2, 16, 4, 'Complex but Rewarding', 'This book requires attention but rewards the reader with a unique and thought-provoking story about reality and existence.', 19),

(4, 26, 4, 'Solid Mystery', 'A classic whodunit with modern sensibilities. Detective Harper is a compelling protagonist and the mystery keeps you guessing.', 16),
(5, 26, 5, 'Perfect Last Case', 'What a perfect send-off for a veteran detective. The case is intricate and the resolution is satisfying.', 21),

(3, 31, 5, 'Life-Changing Read', 'This book completely changed how I think about happiness and well-being. The research is solid and the advice is practical.', 45),
(1, 31, 4, 'Helpful and Insightful', 'Great practical advice backed by science. I''ve already started implementing some of the strategies.', 27),

(2, 36, 5, 'Inspiring Biography', 'Einstein comes alive in this masterful biography. Both his genius and his humanity are beautifully portrayed.', 38),
(4, 36, 4, 'Well-Researched', 'Comprehensive and engaging look at one of history''s greatest minds. Highly recommended for science enthusiasts.', 24);

-- ============================================
-- Insert sample favorites
-- ============================================
INSERT INTO favorites (user_id, book_id) VALUES
(1, 1), (1, 6), (1, 11), (1, 16), (1, 31),
(2, 1), (2, 11), (2, 16), (2, 26), (2, 36),
(3, 1), (3, 6), (3, 31), (3, 41), (3, 46),
(4, 6), (4, 26), (4, 36), (4, 41), (4, 51),
(5, 1), (5, 6), (5, 11), (5, 16), (5, 21);

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