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
        <div class="col-xl-2-4 col-lg-3 col-md-4 col-6 mb-4 favorite-item" data-genre="<?php echo strtolower(str_replace(' ', '-', $book['genre_name'])); ?>" id="favorite-<?php echo $book['book_id']; ?>">
            <div class="book-card position-relative" onclick="window.location.href='book_detail.php?id=<?php echo $book['book_id']; ?>'">
                <img src="assets/images/books/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                <button class="btn btn-sm remove-favorite position-absolute" onclick="event.stopPropagation(); removeFavorite(<?php echo $book['book_id']; ?>, <?php echo $book['favorite_id']; ?>)" title="Remove from favorites" style="top: 10px; right: 10px; z-index: 10;">
                    <i class="fas fa-heart"></i>
                </button>
                <h6 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                <p class="book-author"><?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-rating">
                    <div class="stars">
                        <?php 
                        $rating = $book['avg_rating'] ? round($book['avg_rating']) : 0;
                        for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star star <?php echo $i <= $rating ? 'filled' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
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
        
        // Make AJAX call to backend API
        fetch('../backend/api/favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                book_id: parseInt(bookId),
                action: 'remove'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - animate removal
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
                    
                    // Show success message
                    if (typeof showNotification === 'function') {
                        showNotification('Removed from favorites', 'info');
                    }
                }, 300);
            } else {
                // Error - show message and restore button
                alert(data.message || 'Failed to remove favorite');
                removeBtn.innerHTML = originalHTML;
                removeBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error removing favorite:', error);
            alert('Failed to remove favorite. Please try again.');
            removeBtn.innerHTML = originalHTML;
            removeBtn.disabled = false;
        });
    }
}
</script>

<style>
.remove-favorite {
    opacity: 0;
    transition: all 0.2s ease;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(124, 58, 237, 0.5);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-purple, #7C3AED);
    backdrop-filter: blur(5px);
}

.remove-favorite:hover {
    background: rgba(124, 58, 237, 0.1);
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    color: var(--purple-dark, #5B21B6);
}

.book-card:hover .remove-favorite {
    opacity: 1;
}

.book-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}

.genre-tag {
    background: var(--purple-light);
    color: var(--primary-purple);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-weight: 500;
}

.added-date {
    color: var(--text-light);
}

.col-xl-2-4 {
    flex: 0 0 auto;
    width: 20%;
}

@media (max-width: 1200px) {
    .col-xl-2-4 {
        width: 25%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
