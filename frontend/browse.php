<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'Browse Books - BookVibe';
include 'includes/header.php';

// Static book data using existing images
$sampleBooks = [
    ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'cover' => '1984.jpg', 'rating' => 4.5, 'reviews' => 2847, 'genre' => 'Dystopian Fiction', 'year' => 1949],
    ['id' => 2, 'title' => 'Atomic Habits', 'author' => 'James Clear', 'cover' => 'atomic_habits.jpg', 'rating' => 4.8, 'reviews' => 3214, 'genre' => 'Self-Help', 'year' => 2018],
    ['id' => 3, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'cover' => 'gatsby.jpg', 'rating' => 4.2, 'reviews' => 1892, 'genre' => 'Classic Literature', 'year' => 1925],
    ['id' => 4, 'title' => 'Gone Girl', 'author' => 'Gillian Flynn', 'cover' => 'gone_girl.jpg', 'rating' => 4.3, 'reviews' => 2156, 'genre' => 'Psychological Thriller', 'year' => 2012],
    ['id' => 5, 'title' => 'Little Women', 'author' => 'Louisa May Alcott', 'cover' => 'little_women.jpg', 'rating' => 4.1, 'reviews' => 1678, 'genre' => 'Coming-of-Age', 'year' => 1868],
    ['id' => 6, 'title' => 'The Psychology of Money', 'author' => 'Morgan Housel', 'cover' => 'google_GWorEAAAQBAJ.jpg', 'rating' => 4.6, 'reviews' => 1543, 'genre' => 'Finance', 'year' => 2020],
    ['id' => 7, 'title' => 'Educated', 'author' => 'Tara Westover', 'cover' => 'google_QABREQAAQBAJ.jpg', 'rating' => 4.7, 'reviews' => 2891, 'genre' => 'Memoir', 'year' => 2018],
    ['id' => 8, 'title' => 'The Seven Husbands of Evelyn Hugo', 'author' => 'Taylor Jenkins Reid', 'cover' => 'google_YL_aEAAAQBAJ.jpg', 'rating' => 4.9, 'reviews' => 3456, 'genre' => 'Historical Fiction', 'year' => 2017],
    ['id' => 9, 'title' => 'Where the Crawdads Sing', 'author' => 'Delia Owens', 'cover' => 'google_bXp2EQAAQBAJ.jpg', 'rating' => 4.4, 'reviews' => 2675, 'genre' => 'Mystery', 'year' => 2018],
    ['id' => 10, 'title' => 'Becoming', 'author' => 'Michelle Obama', 'cover' => 'google_iICQDwAAQBAJ.jpg', 'rating' => 4.8, 'reviews' => 4123, 'genre' => 'Biography', 'year' => 2018],
    ['id' => 11, 'title' => 'The Silent Patient', 'author' => 'Alex Michaelides', 'cover' => 'google_mSwvswEACAAJ.jpg', 'rating' => 4.5, 'reviews' => 1967, 'genre' => 'Psychological Thriller', 'year' => 2019],
    ['id' => 12, 'title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'cover' => 'google_s1gVAAAAYAAJ.jpg', 'rating' => 4.3, 'reviews' => 5789, 'genre' => 'Classic Romance', 'year' => 1813]
];

// Static genre data
$genres = [
    ['genre_name' => 'All Books', 'genre_id' => '', 'book_count' => 12],
    ['genre_name' => 'Classic Literature', 'genre_id' => 'classic', 'book_count' => 1],
    ['genre_name' => 'Self-Help', 'genre_id' => 'self-help', 'book_count' => 1],
    ['genre_name' => 'Dystopian Fiction', 'genre_id' => 'dystopian', 'book_count' => 1],
    ['genre_name' => 'Psychological Thriller', 'genre_id' => 'thriller', 'book_count' => 2],
    ['genre_name' => 'Coming-of-Age', 'genre_id' => 'coming-age', 'book_count' => 1],
    ['genre_name' => 'Finance', 'genre_id' => 'finance', 'book_count' => 1],
    ['genre_name' => 'Memoir', 'genre_id' => 'memoir', 'book_count' => 1],
    ['genre_name' => 'Historical Fiction', 'genre_id' => 'historical', 'book_count' => 1],
    ['genre_name' => 'Mystery', 'genre_id' => 'mystery', 'book_count' => 1],
    ['genre_name' => 'Biography', 'genre_id' => 'biography', 'book_count' => 1],
    ['genre_name' => 'Classic Romance', 'genre_id' => 'romance', 'book_count' => 1]
];

// Get filter parameters (static filtering for visual demonstration)
$selectedGenre = $_GET['genre'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'popular';

// Process book covers to use correct paths
foreach ($sampleBooks as $index => $book) {
    $sampleBooks[$index]['cover'] = 'assets/images/books/' . $book['cover'];
}

// Filter books by genre if selected
$filteredBooks = $sampleBooks;
if ($selectedGenre && $selectedGenre !== 'all') {
    $filteredBooks = array_filter($sampleBooks, function($book) use ($selectedGenre) {
        return strtolower(str_replace(' ', '-', $book['genre'])) === $selectedGenre;
    });
}

// Filter by search query if provided
if ($searchQuery) {
    $filteredBooks = array_filter($filteredBooks, function($book) use ($searchQuery) {
        return stripos($book['title'], $searchQuery) !== false || 
               stripos($book['author'], $searchQuery) !== false;
    });
}

$totalResults = count($filteredBooks);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 bg-light">
            <div class="p-4">
                <!-- Search within results -->
                <?php if ($searchQuery): ?>
                <div class="mb-4">
                    <h6>Search Results for: "<?php echo htmlspecialchars($searchQuery); ?>"</h6>
                    <a href="browse.php" class="btn btn-sm btn-outline-secondary">Clear Search</a>
                </div>
                <?php endif; ?>
                
                <!-- Browse by Genre -->
                <div class="mb-4">
                    <h5 class="mb-3">Browse by Genre</h5>
                    <div class="list-group">
                        <?php foreach ($genres as $genre): 
                            $genreKey = $genre['genre_name'] === 'All Books' ? 'all' : strtolower(str_replace(' ', '-', $genre['genre_name']));
                            $isActive = ($selectedGenre === $genreKey) || (!$selectedGenre && $genreKey === 'all');
                        ?>
                        <a href="browse.php?genre=<?php echo urlencode($genreKey); ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $isActive ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($genre['genre_name']); ?>
                            <span class="badge bg-secondary rounded-pill"><?php echo number_format($genre['book_count']); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Sort Options -->
                <div class="mb-4">
                    <h6 class="mb-3">Sort By</h6>
                    <select class="form-select form-control-custom" id="sortBy" onchange="updateSort()">
                        <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="reviews" <?php echo $sortBy === 'reviews' ? 'selected' : ''; ?>>Most Reviewed</option>
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="title_az" <?php echo $sortBy === 'title_az' ? 'selected' : ''; ?>>Title A-Z</option>
                        <option value="title_za" <?php echo $sortBy === 'title_za' ? 'selected' : ''; ?>>Title Z-A</option>
                    </select>
                </div>
                
                <!-- Rating Filter -->
                <div class="mb-4">
                    <h6 class="mb-3">Minimum Rating</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="rating" id="rating-all" value="" checked>
                        <label class="form-check-label" for="rating-all">All Ratings</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="rating" id="rating-4" value="4">
                        <label class="form-check-label" for="rating-4">4+ Stars</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="rating" id="rating-3" value="3">
                        <label class="form-check-label" for="rating-3">3+ Stars</label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="p-4">
                <!-- Header with results count and view toggle -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><?php echo $selectedGenre ? ucfirst(str_replace('-', ' ', $selectedGenre)) . ' Books' : 'All Books'; ?></h2>
                        <p class="text-muted mb-0"><?php echo number_format($totalResults); ?> books found</p>
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
                
                <!-- Books Grid -->
                <div class="row" id="booksContainer">
                    <?php if (empty($filteredBooks)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h4>No books found</h4>
                            <p class="text-muted">Try adjusting your filters or search terms.</p>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php foreach ($filteredBooks as $book): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 book-item">
                            <div class="card book-card h-100">
                                <div class="position-relative">
                                    <img src="<?php echo $book['cover']; ?>" class="card-img-top book-cover" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                    <div class="book-overlay">
                                        <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="book-title mb-2"><?php echo htmlspecialchars($book['title']); ?></h6>
                                    <p class="book-author text-muted mb-2"><?php echo htmlspecialchars($book['author']); ?></p>
                                    <p class="book-genre mb-2"><small class="text-muted"><?php echo htmlspecialchars($book['genre']); ?></small></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="rating">
                                            <?php if ($book['rating'] > 0): ?>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $book['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted ms-1"><?php echo $book['rating']; ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">No ratings yet</small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?php echo number_format($book['reviews']); ?> reviews</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination Placeholder -->
                <nav aria-label="Books pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
function updateSort() {
    const sortValue = document.getElementById('sortBy').value;
    // Static page - just reload with sort parameter for demonstration
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}

function toggleView(view) {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const container = document.getElementById('booksContainer');
    
    if (view === 'grid') {
        gridView.classList.add('active');
        listView.classList.remove('active');
        container.className = 'row';
        document.querySelectorAll('.book-item').forEach(item => {
            item.className = 'col-lg-3 col-md-4 col-sm-6 mb-4 book-item';
        });
    } else {
        listView.classList.add('active');
        gridView.classList.remove('active');
        container.className = 'row';
        document.querySelectorAll('.book-item').forEach(item => {
            item.className = 'col-12 mb-3 book-item';
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
