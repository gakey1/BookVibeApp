<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'BookVibe - Discover Your Next Great Read';
include 'includes/header.php';

// Initialize data arrays
$trendingBooks = [];
$recentReviews = [];
$genres = [];

try {
    $db = Database::getInstance();
    
    // Temporarily force static data to show book covers
    // TODO: Update database books with proper cover_image values
    $trendingBooks = [];
    
    // Get recent reviews
    $recentReviews = $db->fetchAll("
        SELECT r.rating, r.review_text as excerpt, r.created_at,
               u.full_name as user, u.profile_picture as avatar,
               b.title as book
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        JOIN books b ON r.book_id = b.book_id
        WHERE r.is_public = TRUE
        ORDER BY r.created_at DESC
        LIMIT 3
    ");
    
    // Process reviews
    foreach ($recentReviews as &$review) {
        $review['avatar'] = $review['avatar'] ? 'assets/images/profiles/' . $review['avatar'] : 'https://via.placeholder.com/50';
        $review['time'] = timeAgo($review['created_at']);
        $review['excerpt'] = substr($review['excerpt'], 0, 150) . '...';
    }
    
    // Get genres with book counts
    $genres = $db->fetchAll("
        SELECT genre_name as name, genre_icon as icon, 
               (SELECT COUNT(*) FROM books WHERE genre_id = g.genre_id) as count
        FROM genres g
        ORDER BY genre_name ASC
        LIMIT 6
    ");
    
} catch (Exception $e) {
    // Fallback to static data if database fails
    error_log("Database error on homepage: " . $e->getMessage());
}

// Use static data with actual book covers if database query returned empty or failed
if (empty($trendingBooks)) {
    $trendingBooks = [
        ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'cover' => 'assets/images/books/1984.jpg', 'rating' => 4.9, 'reviews' => 2847, 'genre' => 'Dystopian Fiction'],
        ['id' => 2, 'title' => 'Atomic Habits', 'author' => 'James Clear', 'cover' => 'assets/images/books/atomic_habits.jpg', 'rating' => 4.7, 'reviews' => 1923, 'genre' => 'Self-Help'],
        ['id' => 3, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'cover' => 'assets/images/books/gatsby.jpg', 'rating' => 4.5, 'reviews' => 3214, 'genre' => 'Classic Literature'],
        ['id' => 4, 'title' => 'Gone Girl', 'author' => 'Gillian Flynn', 'cover' => 'assets/images/books/gone_girl.jpg', 'rating' => 4.3, 'reviews' => 2156, 'genre' => 'Psychological Thriller'],
        ['id' => 5, 'title' => 'Little Women', 'author' => 'Louisa May Alcott', 'cover' => 'assets/images/books/little_women.jpg', 'rating' => 4.1, 'reviews' => 1678, 'genre' => 'Coming-of-Age']
    ];
}

// Fallback data for other sections if needed
if (empty($recentReviews)) {
    $recentReviews = [
        ['user' => 'Demo User', 'avatar' => 'assets/images/profiles/default.jpg', 'book' => 'Sample Book', 'rating' => 5, 'excerpt' => 'Great book! Really enjoyed reading it...', 'time' => '2 hours ago']
    ];
}

if (empty($genres)) {
    $genres = [
        ['name' => 'Romance', 'icon' => 'fa-heart', 'count' => 2341],
        ['name' => 'Thriller', 'icon' => 'fa-mask', 'count' => 1876],
        ['name' => 'Fantasy', 'icon' => 'fa-dragon', 'count' => 2987]
    ];
}

// Time ago function
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    return floor($time/86400) . ' days ago';
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1 class="mb-3">Discover Your Next Great Read</h1>
                <p class="mb-4">Join thousands of readers exploring, reviewing, and collecting their favorite books in one beautiful platform.</p>
                <div class="d-flex gap-3">
                    <a href="browse.php" class="btn btn-light btn-lg">
                        <i class="fas fa-compass me-2"></i>Explore Books
                    </a>
                    <?php if (!$isLoggedIn): ?>
                    <a href="../backend/register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-users me-2"></i>Join Community
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 hero-books d-none d-lg-block">
                <div class="position-relative">
                    <!-- Decorative book images using actual covers -->
                    <img src="assets/images/books/1984.jpg" 
                         class="hero-book" style="left: 50px; top: 20px; transform: rotate(-10deg);" alt="1984">
                    <img src="assets/images/books/google_iICQDwAAQBAJ.jpg" 
                         class="hero-book" style="left: 200px; top: 0; transform: rotate(5deg);" alt="Featured Book">
                    <img src="assets/images/books/gatsby.jpg" 
                         class="hero-book" style="left: 350px; top: 30px; transform: rotate(-8deg);" alt="The Great Gatsby">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Books Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Trending This Week</h2>
            <a href="browse.php?sort=trending" class="btn btn-ghost">View All →</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($trendingBooks as $book): ?>
            <div class="col-xl-2-4 col-lg-3 col-md-4 col-6">
                <div class="book-card" onclick="window.location.href='book_detail.php?id=<?php echo $book['id']; ?>'">
                    <img src="<?php echo $book['cover']; ?>" alt="<?php echo $book['title']; ?>" class="book-cover">
                    <h6 class="book-title"><?php echo $book['title']; ?></h6>
                    <p class="book-author"><?php echo $book['author']; ?></p>
                    <div class="book-rating">
                        <div class="stars">
                            <?php 
                            $fullStars = floor($book['rating']);
                            $halfStar = $book['rating'] - $fullStars >= 0.5;
                            
                            for ($i = 0; $i < $fullStars; $i++) {
                                echo '<i class="fas fa-star star filled"></i>';
                            }
                            if ($halfStar) {
                                echo '<i class="fas fa-star-half-alt star filled"></i>';
                            }
                            for ($i = $fullStars + ($halfStar ? 1 : 0); $i < 5; $i++) {
                                echo '<i class="far fa-star star empty"></i>';
                            }
                            ?>
                        </div>
                        <span class="rating-number"><?php echo $book['rating']; ?></span>
                    </div>
                    <p class="review-count"><?php echo number_format($book['reviews']); ?> reviews</p>
                    <span class="genre-badge"><?php echo $book['genre']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Genre Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2>Explore by Genre</h2>
            <p class="text-muted">Find your perfect book in your favorite category</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($genres as $genre): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="browse.php?genre=<?php echo urlencode($genre['name']); ?>" class="text-decoration-none">
                    <div class="genre-circle">
                        <i class="fas <?php echo $genre['icon']; ?>"></i>
                        <span class="genre-name"><?php echo htmlspecialchars($genre['name']); ?></span>
                    </div>
                    <p class="text-center mt-2 text-muted"><?php echo number_format($genre['count']); ?> books</p>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Recent Reviews Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Recent Reviews</h2>
            <a href="browse.php?view=reviews" class="btn btn-ghost">See All Reviews →</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($recentReviews as $review): ?>
            <div class="col-lg-4 col-md-6">
                <div class="review-card">
                    <div class="review-header">
                        <img src="<?php echo $review['avatar']; ?>" alt="<?php echo $review['user']; ?>" class="review-avatar">
                        <div>
                            <div class="review-user"><?php echo $review['user']; ?></div>
                            <div class="review-date"><?php echo $review['time']; ?></div>
                        </div>
                    </div>
                    <div class="book-rating mb-2">
                        <div class="stars">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                <i class="fas fa-star star filled"></i>
                            <?php endfor; ?>
                            <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                <i class="far fa-star star empty"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="fw-bold mb-2">Review of "<?php echo $review['book']; ?>"</p>
                    <p class="review-text"><?php echo $review['excerpt']; ?></p>
                    <button class="helpful-btn">
                        <i class="fas fa-thumbs-up me-1"></i> Helpful
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Choose BookVibe?</h2>
            <p class="text-muted">Everything you need for your reading journey</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-book-reader fa-3x text-purple"></i>
                    </div>
                    <h5>Discover Books</h5>
                    <p class="text-muted">Explore our vast collection of books across all genres</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-star fa-3x text-purple"></i>
                    </div>
                    <h5>Write Reviews</h5>
                    <p class="text-muted">Share your thoughts and help others find great reads</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-heart fa-3x text-purple"></i>
                    </div>
                    <h5>Build Your Library</h5>
                    <p class="text-muted">Create your personal collection of favorite books</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-purple"></i>
                    </div>
                    <h5>Join Community</h5>
                    <p class="text-muted">Connect with fellow readers and share recommendations</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3>Stay Updated</h3>
                <p class="text-muted mb-4">Get the latest book recommendations and reviews delivered to your inbox</p>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control form-control-custom" placeholder="Enter your email">
                    <button type="submit" class="btn btn-primary-custom">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Custom CSS for 5-column layout -->
<style>
@media (min-width: 1200px) {
    .col-xl-2-4 {
        flex: 0 0 20%;
        max-width: 20%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>