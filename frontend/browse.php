<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'Browse Books - BookVibe';

// Include database connection
require_once '../config/db.php';

include 'includes/header.php';

// Get filter parameters
$selectedGenre = $_GET['genre'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'popular';

try {
    // Get genres from database
    $genresQuery = "SELECT genre_id, genre_name, book_count FROM genres ORDER BY display_order ASC, genre_name ASC";
    $genresStmt = $pdo->prepare($genresQuery);
    $genresStmt->execute();
    $dbGenres = $genresStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add "All Books" option
    $allBooksCount = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $genres = [['genre_name' => 'All Books', 'genre_id' => '', 'book_count' => $allBooksCount]];
    $genres = array_merge($genres, $dbGenres);

    // Build WHERE clause for filtering
    $whereConditions = [];
    $params = [];
    
    // Genre filtering
    if ($selectedGenre && $selectedGenre !== 'all' && is_numeric($selectedGenre)) {
        $whereConditions[] = "b.genre_id = ?";
        $params[] = $selectedGenre;
    }
    
    // Search filtering
    if ($searchQuery) {
        $whereConditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
        $searchTerm = '%' . $searchQuery . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Build ORDER BY clause
    $orderBy = "ORDER BY ";
    switch ($sortBy) {
        case 'rating':
            $orderBy .= "b.avg_rating DESC, b.review_count DESC";
            break;
        case 'reviews':
            $orderBy .= "b.review_count DESC, b.avg_rating DESC";
            break;
        case 'newest':
            $orderBy .= "b.publication_year DESC, b.title ASC";
            break;
        case 'title_az':
            $orderBy .= "b.title ASC";
            break;
        case 'title_za':
            $orderBy .= "b.title DESC";
            break;
        case 'popular':
        default:
            $orderBy .= "(b.avg_rating * 0.7 + (b.review_count / 10) * 0.3) DESC, b.title ASC";
            break;
    }
    
    // Get books from database
    $booksQuery = "
        SELECT 
            b.book_id as id,
            b.title,
            b.author,
            b.cover_image as cover,
            b.avg_rating as rating,
            b.review_count as reviews,
            b.publication_year as year,
            g.genre_name as genre
        FROM books b
        LEFT JOIN genres g ON b.genre_id = g.genre_id
        $whereClause
        $orderBy
    ";
    
    $booksStmt = $pdo->prepare($booksQuery);
    $booksStmt->execute($params);
    $books = $booksStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process book covers to use correct paths
    foreach ($books as $index => $book) {
        $books[$index]['cover'] = 'assets/images/books/' . $book['cover'];
    }
    
    $totalResults = count($books);
    
} catch (PDOException $e) {
    // Fallback to empty data if database connection fails
    error_log("Database error in browse.php: " . $e->getMessage());
    $genres = [['genre_name' => 'All Books', 'genre_id' => '', 'book_count' => 0]];
    $books = [];
    $totalResults = 0;
}
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
                            $genreKey = $genre['genre_name'] === 'All Books' ? 'all' : $genre['genre_id'];
                            $isActive = ($selectedGenre == $genreKey) || (!$selectedGenre && $genreKey === 'all');
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
                        <?php 
                        $pageHeader = 'All Books';
                        if ($selectedGenre && is_numeric($selectedGenre)) {
                            foreach ($genres as $genre) {
                                if ($genre['genre_id'] == $selectedGenre) {
                                    $pageHeader = $genre['genre_name'] . ' Books';
                                    break;
                                }
                            }
                        }
                        ?>
                        <h2><?php echo htmlspecialchars($pageHeader); ?></h2>
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
                    <?php if (empty($books)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h4>No books found</h4>
                            <p class="text-muted">Try adjusting your filters or search terms.</p>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
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
