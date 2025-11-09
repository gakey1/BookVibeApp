<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'My Favorites - BookVibe';
include 'includes/header.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

// Database logic to fetch user's favorites
require_once __DIR__ . '/../config/db.php'; 
$db = Database::getInstance();

$user_id = $_SESSION['user_id'];

// Fetch user's favorite books with book details
$sql_favorites = "
    SELECT 
        f.favorite_id,
        f.added_at as date_added,
        b.*,
        g.genre_name,
        AVG(r.rating) as avg_rating,
        COUNT(r.review_id) as review_count
    FROM 
        favorites f
    JOIN 
        books b ON f.book_id = b.book_id
    LEFT JOIN 
        genres g ON b.genre_id = g.genre_id
    LEFT JOIN 
        reviews r ON b.book_id = r.book_id
    WHERE 
        f.user_id = ?
    GROUP BY 
        f.favorite_id, f.added_at, b.book_id, g.genre_name
    ORDER BY 
        f.added_at DESC";

$favoriteBooks = $db->fetchAll($sql_favorites, [$user_id]);

$totalBooks = count($favoriteBooks);
$uniqueGenres = 0;
$avgRating = 0;

if ($totalBooks > 0) {
    $genres = array_unique(array_column($favoriteBooks, 'genre_name'));
    $uniqueGenres = count($genres);
    $ratings = array_filter(array_column($favoriteBooks, 'avg_rating'));
    $avgRating = !empty($ratings) ? round(array_sum($ratings) / count($ratings), 1) : 0;
}
?>

<div class="container my-5">
    <?php if (!$isLoggedIn): ?>
    <!-- Not logged in message -->
    <div class="text-center py-5">
        <i class="fas fa-heart fa-4x text-muted mb-4"></i>
        <h2>Please Log In</h2>
        <p class="text-muted mb-4">You need to be logged in to view your favorite books.</p>
        <a href="login.php" class="btn btn-primary">Log In</a>
    </div>
    <?php else: ?>
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-2">My Favorites</h1>
            <p class="text-muted mb-0">Your personal collection of beloved books</p>
            <small class="text-muted"><?php echo number_format($totalBooks); ?> books saved</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-purple" onclick="exportFavorites()">
                <i class="fas fa-download me-2"></i>Export List
            </button>
        </div>
    </div>
    
    <!-- Sort and Filter Controls -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-3">
            <div>
                <label class="form-label mb-1">Sort by:</label>
                <select class="form-select form-select-sm" style="width: auto;" id="sortFavorites">
                    <option value="recent">Latest Added</option>
                    <option value="oldest">Oldest Added</option>
                    <option value="rating-high">Highest Rating</option>
                    <option value="rating-low">Lowest Rating</option>
                    <option value="title-az">Title A-Z</option>
                    <option value="title-za">Title Z-A</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1">Genre:</label>
                <select class="form-select form-select-sm" style="width: auto;" id="filterGenre">
                    <option value="">All Genres</option>
                    <option value="fiction">Fiction</option>
                    <option value="romance">Romance</option>
                    <option value="thriller">Thriller</option>
                    <option value="sci-fi">Sci-Fi</option>
                    <option value="fantasy">Fantasy</option>
                    <option value="mystery">Mystery</option>
                    <option value="non-fiction">Non-Fiction</option>
                    <option value="biography">Biography</option>
                    <option value="history">History</option>
                    <option value="adventure">Adventure</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm active" id="gridView" onclick="toggleView('grid')">
                <i class="fas fa-th"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm" id="listView" onclick="toggleView('list')">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>


    <!-- Favorites Content -->
    <?php if (empty($favoriteBooks)): ?>
    <!-- Empty State -->
    <div class="text-center py-5">
        <i class="fas fa-heart-broken fa-4x text-muted mb-4"></i>
        <h3>No favorites yet</h3>
        <p class="text-muted mb-4">Start building your personal library by adding books to your favorites!</p>
        <a href="browse.php" class="btn btn-primary">
            <i class="fas fa-search me-2"></i>Browse Books
        </a>
    </div>
    <?php else: ?>
    

    <!-- Favorites Grid -->
    <div class="row" id="favoritesContainer">
        <?php foreach ($favoriteBooks as $book): ?>
        <div class="col-xl-2-4 col-lg-3 col-md-4 col-6 mb-4 favorite-item" 
             data-genre="<?php echo strtolower(str_replace(' ', '-', $book['genre_name'])); ?>" 
             data-date-added="<?php echo $book['date_added']; ?>"
             id="favorite-<?php echo $book['book_id']; ?>">
            <div class="book-card position-relative" onclick="window.location.href='book_detail.php?id=<?php echo $book['book_id']; ?>'">
                <img src="assets/images/books/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                <button class="btn btn-sm remove-favorite position-absolute" onclick="event.stopPropagation(); removeFavorite(<?php echo $book['book_id']; ?>, <?php echo $book['favorite_id']; ?>)" title="Remove from favorites" style="top: 10px; right: 10px; z-index: 10;">
                    <i class="fas fa-heart text-danger"></i>
                </button>
                <h6 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                <p class="book-author"><?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-rating" data-rating="<?php echo $book['avg_rating'] ? $book['avg_rating'] : 0; ?>">
                    <!-- Stars will be updated by JavaScript -->
                    <span class="rating-text"><?php echo $book['avg_rating'] ? number_format($book['avg_rating'], 1) : 'N/A'; ?> (<?php echo number_format($book['review_count']); ?>)</span>
                </div>
                <div class="book-meta">
                    <span class="genre-tag"><?php echo htmlspecialchars($book['genre_name']); ?></span>
                    <span class="added-date">Added <?php echo date('M j', strtotime($book['date_added'])); ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Load More Button -->
    <?php if (!empty($favoriteBooks) && count($favoriteBooks) >= 12): ?>
    <div class="text-center mt-4" id="loadMoreContainer">
        <button class="btn btn-outline-primary" onclick="loadMoreFavorites()">
            Load More Books
        </button>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Reading Stats Section -->
    <?php if (!empty($favoriteBooks)): ?>
    <div class="mt-5">
        <h4 class="text-center mb-4">Your Reading Stats</h4>
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h2 class="text-primary mb-0"><?php echo $totalBooks; ?></h2>
                    <small class="text-muted">Books Favorited</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h2 class="text-success mb-0"><?php echo $uniqueGenres; ?></h2>
                    <small class="text-muted">Genres Explored</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h2 class="text-warning mb-0"><?php echo $avgRating; ?></h2>
                    <small class="text-muted">Average Rating</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h2 class="text-info mb-0">24</h2>
                    <small class="text-muted">Reviews Written</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>


<!-- Page-specific CSS -->
<link rel="stylesheet" href="assets/css/favorites.css?v=<?php echo time(); ?>">

<!-- Page-specific JavaScript -->
<script src="assets/js/favorites.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/footer.php'; ?>
