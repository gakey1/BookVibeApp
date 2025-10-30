<?php 
// Define app constant for config access
define('BOOK_REVIEW_APP', true);

$pageTitle = 'My Favorites - Book Review Website';
include 'includes/header.php';

// Static favorites data using existing images
$favoriteBooks = [
    ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'cover' => 'assets/images/books/1984.jpg', 'rating' => 4.5, 'reviews' => 2847, 'genre' => 'Dystopian Fiction', 'date_added' => 'October 15, 2024'],
    ['id' => 3, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'cover' => 'assets/images/books/gatsby.jpg', 'rating' => 4.2, 'reviews' => 1892, 'genre' => 'Classic Literature', 'date_added' => 'October 12, 2024'],
    ['id' => 2, 'title' => 'Atomic Habits', 'author' => 'James Clear', 'cover' => 'assets/images/books/atomic_habits.jpg', 'rating' => 4.8, 'reviews' => 3214, 'genre' => 'Self-Help', 'date_added' => 'October 8, 2024']
];

// Check if user is logged in (simulate login for demo)
$isLoggedIn = isset($_SESSION['user_id']) || true; // Set to true for demo purposes
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
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary"><?php echo count($favoriteBooks); ?></h4>
                    <small class="text-muted">Favorite Books</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success">2</h4>
                    <small class="text-muted">Books Read</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning">1</h4>
                    <small class="text-muted">Currently Reading</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info">4.5</h4>
                    <small class="text-muted">Avg Rating Given</small>
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
        <div class="col-lg-4 col-md-6 mb-4 favorite-item" data-genre="<?php echo strtolower(str_replace(' ', '-', $book['genre'])); ?>">
            <div class="card favorite-card h-100">
                <div class="position-relative">
                    <img src="<?php echo $book['cover']; ?>" class="card-img-top book-cover" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <div class="book-overlay">
                        <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                    <button class="btn btn-sm btn-danger remove-favorite" onclick="removeFavorite(<?php echo $book['id']; ?>)" title="Remove from favorites">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                <div class="card-body">
                    <h6 class="book-title mb-2"><?php echo htmlspecialchars($book['title']); ?></h6>
                    <p class="book-author text-muted mb-2"><?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="book-genre mb-2">
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($book['genre']); ?></span>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $book['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                            <?php endfor; ?>
                            <small class="text-muted ms-1"><?php echo $book['rating']; ?></small>
                        </div>
                        <small class="text-muted"><?php echo number_format($book['reviews']); ?> reviews</small>
                    </div>
                    
                    <small class="text-muted">Added: <?php echo $book['date_added']; ?></small>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-grid gap-2">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-book-reader me-1"></i>Read
                            </button>
                            <button class="btn btn-outline-success btn-sm">
                                <i class="fas fa-check me-1"></i>Mark as Read
                            </button>
                        </div>
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
function removeFavorite(bookId) {
    if (confirm('Remove this book from your favorites?')) {
        // Static demo - would make API call here
        alert('Book removed from favorites! (Demo mode - not actually removed)');
        // Could hide the element for demo:
        // event.target.closest('.favorite-item').style.display = 'none';
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