<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'My Favorites - BookVibe';
include 'includes/header.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header('Location: ../backend/login.php');
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
        f.created_at as date_added,
        b.*,
        g.name as genre_name,
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
        f.favorite_id, f.created_at, b.book_id, g.name
    ORDER BY 
        f.created_at DESC";

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-heart text-danger me-2"></i>My Favorites</h1>
            <p class="text-muted">Books you've saved for later reading</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm active" id="gridView" onclick="toggleView('grid')">
                <i class="fas fa-th"></i> Grid
            </button>
            <button class="btn btn-outline-secondary btn-sm" id="listView" onclick="toggleView('list')">
                <i class="fas fa-list"></i> List
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary"><?php echo $totalBooks; ?></h4>
                    <small class="text-muted">Favorite Books</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success"><?php echo $uniqueGenres; ?></h4>
                    <small class="text-muted">Unique Genres</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning"><?php echo $avgRating; ?></h4>
                    <small class="text-muted">Avg Rating</small>
                </div>
            </div>
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
    
    <!-- Filter and Sort -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Search your favorites..." id="searchFavorites">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="sortFavorites">
                <option value="recent">Recently Added</option>
                <option value="title">Title A-Z</option>
                <option value="author">Author A-Z</option>
                <option value="rating">Highest Rated</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterGenre">
                <option value="">All Genres</option>
                <option value="classic">Classic Literature</option>
                <option value="dystopian">Dystopian Fiction</option>
                <option value="self-help">Self-Help</option>
            </select>
        </div>
    </div>

    <!-- Favorites Grid -->
    <div class="row" id="favoritesContainer">
        <?php foreach ($favoriteBooks as $book): ?>
        <div class="col-lg-4 col-md-6 mb-4 favorite-item" data-genre="<?php echo strtolower(str_replace(' ', '-', $book['genre_name'])); ?>" id="favorite-<?php echo $book['book_id']; ?>">
            <div class="card favorite-card h-100">
                <div class="position-relative">
                    <img src="assets/images/books/<?php echo htmlspecialchars($book['cover_image']); ?>" class="card-img-top book-cover" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <div class="book-overlay">
                        <a href="book_detail.php?id=<?php echo $book['book_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                    <button class="btn btn-sm btn-danger remove-favorite" onclick="removeFavorite(<?php echo $book['book_id']; ?>, <?php echo $book['favorite_id']; ?>)" title="Remove from favorites">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                <div class="card-body">
                    <h6 class="book-title mb-2"><?php echo htmlspecialchars($book['title']); ?></h6>
                    <p class="book-author text-muted mb-2"><?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="book-genre mb-2">
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($book['genre_name']); ?></span>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rating">
                            <?php 
                            $rating = $book['avg_rating'] ? round($book['avg_rating']) : 0;
                            for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?> small"></i>
                            <?php endfor; ?>
                            <small class="text-muted ms-1"><?php echo $book['avg_rating'] ? number_format($book['avg_rating'], 1) : 'N/A'; ?></small>
                        </div>
                        <small class="text-muted"><?php echo number_format($book['review_count']); ?> reviews</small>
                    </div>
                    
                    <small class="text-muted">Added: <?php echo date('M j, Y', strtotime($book['date_added'])); ?></small>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-grid gap-2">
                        <a href="book_detail.php?id=<?php echo $book['book_id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<script>
// Search functionality
document.getElementById('searchFavorites')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const favorites = document.querySelectorAll('.favorite-item');
    
    favorites.forEach(item => {
        const title = item.querySelector('.book-title').textContent.toLowerCase();
        const author = item.querySelector('.book-author').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Sort functionality
document.getElementById('sortFavorites')?.addEventListener('change', function() {
    const sortBy = this.value;
    // Static demo - would implement sorting logic here
    console.log('Sorting by:', sortBy);
});

// Genre filter
document.getElementById('filterGenre')?.addEventListener('change', function() {
    const selectedGenre = this.value;
    const favorites = document.querySelectorAll('.favorite-item');
    
    favorites.forEach(item => {
        const genre = item.dataset.genre;
        
        if (!selectedGenre || genre === selectedGenre) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Toggle view
function toggleView(view) {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const container = document.getElementById('favoritesContainer');
    
    if (view === 'grid') {
        gridView.classList.add('active');
        listView.classList.remove('active');
        container.className = 'row';
        document.querySelectorAll('.favorite-item').forEach(item => {
            item.className = 'col-lg-4 col-md-6 mb-4 favorite-item';
        });
    } else {
        listView.classList.add('active');
        gridView.classList.remove('active');
        container.className = 'row';
        document.querySelectorAll('.favorite-item').forEach(item => {
            item.className = 'col-12 mb-3 favorite-item';
        });
    }
}

// Remove favorite
function removeFavorite(bookId, favoriteId) {
    if (confirm('Remove this book from your favorites?')) {
        // Show loading state
        const bookCard = document.getElementById('favorite-' + bookId);
        const removeBtn = bookCard.querySelector('.remove-favorite');
        const originalHTML = removeBtn.innerHTML;
        
        removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        removeBtn.disabled = true;
        
        // For now, simulate removal since Tracy's API only handles adding
        // In future, we would extend her API to handle DELETE requests
        setTimeout(function() {
            bookCard.style.opacity = '0.5';
            bookCard.style.transform = 'scale(0.8)';
            
            setTimeout(function() {
                bookCard.remove();
                
                // Update stats if needed
                const statsContainer = document.querySelector('.text-primary');
                if (statsContainer) {
                    const currentCount = parseInt(statsContainer.textContent);
                    if (currentCount > 1) {
                        statsContainer.textContent = currentCount - 1;
                    } else {
                        // Reload page if no more favorites
                        window.location.reload();
                    }
                }
            }, 300);
        }, 1000);
    }
}
</script>

<style>
.favorite-card {
    transition: transform 0.2s;
}

.favorite-card:hover {
    transform: translateY(-5px);
}

.remove-favorite {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    opacity: 0;
    transition: opacity 0.2s;
}

.favorite-card:hover .remove-favorite {
    opacity: 1;
}

.book-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.favorite-card:hover .book-overlay {
    opacity: 1;
}

.book-cover {
    height: 300px;
    object-fit: cover;
}
</style>

<?php include 'includes/footer.php'; ?>
