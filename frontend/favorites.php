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
                    <option value="recent">Date Added</option>
                    <option value="title">Title</option>
                    <option value="author">Author</option>
                    <option value="rating">Rating</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1">Genre:</label>
                <select class="form-select form-select-sm" style="width: auto;" id="filterGenre">
                    <option value="">All Genres</option>
                    <option value="classic">Classic Literature</option>
                    <option value="dystopian">Dystopian Fiction</option>
                    <option value="self-help">Self-Help</option>
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
        <div class="col-xl-2-4 col-lg-3 col-md-4 col-6 mb-4 favorite-item" data-genre="<?php echo strtolower(str_replace(' ', '-', $book['genre_name'])); ?>" id="favorite-<?php echo $book['book_id']; ?>">
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
    <?php if (!empty($favoriteBooks)): ?>
    <div class="text-center mt-4">
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

// Export favorites function
function exportFavorites() {
    // Simple CSV export functionality
    const favoriteItems = document.querySelectorAll('.favorite-item:not([style*="display: none"])');
    let csvContent = 'Title,Author,Genre,Rating\\n';
    
    favoriteItems.forEach(item => {
        const title = item.querySelector('.book-title').textContent;
        const author = item.querySelector('.book-author').textContent;
        const genre = item.querySelector('.genre-tag').textContent;
        const rating = item.querySelector('.rating-text').textContent;
        csvContent += `"${title}","${author}","${genre}","${rating}"\\n`;
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'my-favorite-books.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Load more books function (placeholder)
function loadMoreFavorites() {
    // This would normally load more books from the database
    alert('Load more functionality would fetch additional favorite books from the database.');
}
</script>

<style>
.remove-favorite {
    opacity: 0;
    transition: all 0.2s ease;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.remove-favorite:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.remove-favorite.favorited {
    background: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.5);
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

.stat-item {
    padding: 1rem;
}

.stat-item h2 {
    font-weight: 600;
    font-size: 2rem;
}

.btn-purple {
    background: var(--primary-purple, #7C3AED);
    border-color: var(--primary-purple, #7C3AED);
    color: white;
}

.btn-purple:hover {
    background: var(--purple-dark, #5B21B6);
    border-color: var(--purple-dark, #5B21B6);
    color: white;
}

@media (max-width: 1200px) {
    .col-xl-2-4 {
        width: 25%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
