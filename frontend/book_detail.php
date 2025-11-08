<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

// Include database connection
require_once '../config/db.php';

// Include header
include 'includes/header.php';

// Get the Book ID securely from the URL
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($bookId === 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Error: Book ID is required.</div></div>';
    include 'includes/footer.php';
    exit;
}

try {
    // Fetch Single Book Metadata
    $sql_book = "
        SELECT 
            b.*, 
            g.genre_name
        FROM 
            books b
        LEFT JOIN
            genres g ON b.genre_id = g.genre_id
        WHERE 
            b.book_id = ?";
    
    $stmt = $pdo->prepare($sql_book);
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo '<div class="container mt-5"><div class="alert alert-danger">Error: Book not found.</div></div>';
        include 'includes/footer.php';
        exit;
    }

    // Process book data
    $book['rating'] = round($book['avg_rating'], 1);
    $book['reviews'] = $book['review_count'];
    $book['cover'] = 'assets/images/books/' . $book['cover_image'];

    // Fetch Individual Reviews
    $sql_reviews = "
        SELECT 
            r.*, 
            u.full_name AS user_name,
            u.profile_picture AS avatar
        FROM 
            reviews r
        JOIN 
            users u ON r.user_id = u.user_id
        WHERE 
            r.book_id = ?
        ORDER BY 
            r.created_at DESC
    ";
    $stmt = $pdo->prepare($sql_reviews);
    $stmt->execute([$bookId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process review avatars with fallback
    foreach ($reviews as &$review) {
        if ($review['avatar'] && $review['avatar'] !== 'default.jpg' && $review['avatar'] !== 'default.svg' && $review['avatar'] !== '') {
            $review['avatar'] = 'assets/images/profiles/' . $review['avatar'];
        } else {
            $review['avatar'] = 'assets/images/profiles/default.svg';
        }
    }

    // Fetch Ratings Breakdown
    $ratingsBreakdown = [];
    $sql_breakdown = "
        SELECT 
            rating, 
            COUNT(review_id) AS count
        FROM 
            reviews
        WHERE 
            book_id = ?
        GROUP BY 
            rating
        ORDER BY 
            rating DESC";
    $stmt = $pdo->prepare($sql_breakdown);
    $stmt->execute([$bookId]);
    $breakdown_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert results to the required format (5 => percentage, 4 => percentage, etc.)
    $total_reviews_count = $book['reviews'];
    for ($i = 5; $i >= 1; $i--) {
        $found = false;
        foreach ($breakdown_results as $row) {
            if ($row['rating'] == $i) {
                // Calculate percentage
                $ratingsBreakdown[$i] = $total_reviews_count > 0 ? 
                                        round(($row['count'] / $total_reviews_count) * 100) : 0;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $ratingsBreakdown[$i] = 0;
        }
    }

} catch (PDOException $e) {
    error_log("Database error in book_detail.php: " . $e->getMessage());
    echo '<div class="container mt-5"><div class="alert alert-danger">Error: Unable to load book details.</div></div>';
    include 'includes/footer.php';
    exit;
}

$pageTitle = htmlspecialchars($book['title']) . ' - BookVibe';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="browse.php">Books</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($book['title']); ?></li>
    </ol>
</nav>

<div class="container my-5">
    <div class="row">
        <!-- Book Cover -->
        <div class="col-lg-4 mb-4">
            <div class="position-relative">
                <img src="<?php echo $book['cover']; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" 
                     class="img-fluid rounded shadow book-detail-cover">
            </div>
            
            <!-- Action Buttons -->
            <div class="d-grid gap-2 mt-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    // Check if book is already favorited
                    $fav_check = $pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND book_id = ?");
                    $fav_check->execute([$_SESSION['user_id'], $book['book_id']]);
                    $is_favorited = $fav_check->fetch();
                    ?>
                    <button class="btn btn-primary btn-lg favorite-btn <?php echo $is_favorited ? 'favorited' : ''; ?>" 
                            data-book-id="<?php echo $book['book_id']; ?>"
                            onclick="toggleBookFavorite(this, <?php echo $book['book_id']; ?>)">
                        <?php if ($is_favorited): ?>
                            <i class="fas fa-heart text-danger me-2"></i>Favorited
                        <?php else: ?>
                            <i class="far fa-heart text-danger me-2"></i>Add to Favorites
                        <?php endif; ?>
                    </button>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Save
                </a>
                <?php endif; ?>
                
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-secondary" onclick="showComingSoon(event)">
                        <i class="fas fa-book-reader me-2"></i>Preview
                    </button>
                    <button class="btn btn-outline-secondary" onclick="shareBook()">
                        <i class="fas fa-share-alt me-2"></i>Share
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Book Details -->
        <div class="col-lg-8">
            <h1 class="mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
            <p class="text-muted mb-3">by <a href="browse.php?search=<?php echo urlencode($book['author']); ?>" 
                                            class="text-decoration-none"><?php echo htmlspecialchars($book['author']); ?></a></p>
            
            <!-- Rating -->
            <div class="d-flex align-items-center mb-3">
                <div class="rating me-2" data-rating="<?php echo $book['rating']; ?>">
                    <!-- Stars will be updated by JavaScript -->
                </div>
                <span class="me-3"><?php echo number_format($book['rating'], 1); ?> out of 5</span>
                <span class="text-muted">(<?php echo number_format($book['reviews']); ?> reviews)</span>
            </div>
            
            <!-- Book Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <?php if ($book['isbn']): ?>
                        <tr>
                            <td class="text-muted">ISBN:</td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($book['publisher']): ?>
                        <tr>
                            <td class="text-muted">Publisher:</td>
                            <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($book['publication_year']): ?>
                        <tr>
                            <td class="text-muted">Year:</td>
                            <td><?php echo htmlspecialchars($book['publication_year']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($book['page_count'] && $book['page_count'] > 0): ?>
                        <tr>
                            <td class="text-muted">Pages:</td>
                            <td><?php echo number_format($book['page_count']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Genre:</td>
                            <td>
                                <a href="browse.php?genre=<?php echo urlencode(strtolower(str_replace(' ', '-', $book['genre_name']))); ?>" 
                                   class="badge bg-secondary text-decoration-none">
                                    <?php echo htmlspecialchars($book['genre_name']); ?>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Description -->
            <div class="mb-4">
                <h4>Description</h4>
                <div id="bookDescription" class="book-description">
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>
                <?php if (strlen($book['description']) > 500): ?>
                <button class="btn btn-link p-0" onclick="toggleDescription()">Show more</button>
                <?php endif; ?>
            </div>
            
            <!-- Rating Breakdown -->
            <div class="mb-4">
                <h4>Rating Breakdown</h4>
                <div class="rating-breakdown">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2"><?php echo $star; ?> star</span>
                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?php echo $ratingsBreakdown[$star]; ?>%"
                                 aria-valuenow="<?php echo $ratingsBreakdown[$star]; ?>" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="text-muted"><?php echo $ratingsBreakdown[$star]; ?>%</span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Community Reviews</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                    <i class="fas fa-pen me-2"></i>Write a Review
                </button>
                <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary">Login to Review</a>
                <?php endif; ?>
            </div>
            
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                <div class="review-card mb-4 p-4 bg-light rounded">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $review['avatar']; ?>?t=<?php echo time(); ?>" alt="User" 
                                 class="rounded-circle me-3" width="50" height="50"
                                 onerror="this.src='assets/images/profiles/default.svg'"
                                 style="background: #f8f9fa; border: 1px solid #e9ecef;">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                <div class="rating" data-rating="<?php echo $review['rating']; ?>">
                                    <!-- Stars will be updated by JavaScript -->
                                    <span class="text-muted ms-2"><?php echo $review['created_at']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Write Review Modal -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="writeReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Write a Review for "<?php echo htmlspecialchars($book['title']); ?>"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Your Rating</label>
                        <div class="star-rating-input manual-stars" data-input-rating="0">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star star" data-star-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewText" class="form-label">Your Review</label>
                        <textarea class="form-control" id="reviewText" name="review_text" 
                                  rows="5" maxlength="1000" required></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/1000 characters
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReview()">Submit Review</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="assets/css/book_detail.css?v=<?php echo time(); ?>">

<!-- Page-specific JavaScript -->
<script src="assets/js/book_detail.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/footer.php'; ?>
