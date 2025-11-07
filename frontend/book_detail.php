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

    // Process review avatars
    foreach ($reviews as &$review) {
        $review['avatar'] = $review['avatar'] ? 'assets/images/profiles/' . $review['avatar'] : 'assets/images/profiles/default.jpg';
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
                            data-book-id="<?php echo $book['book_id']; ?>">
                        <?php if ($is_favorited): ?>
                            <i class="fas fa-heart me-2" style="color: var(--primary-purple, #7C3AED);"></i>Favorited
                        <?php else: ?>
                            <i class="far fa-heart me-2" style="color: var(--text-light, #6B7280);"></i>Add to Favorites
                        <?php endif; ?>
                    </button>
                <?php else: ?>
                <a href="../backend/login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Save
                </a>
                <?php endif; ?>
                
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-secondary">
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
                <div class="rating me-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $book['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                    <?php endfor; ?>
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
                                 class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                                    <?php endfor; ?>
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
                        <div class="star-rating-input" data-rating="0">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star star" data-rating="<?php echo $i; ?>"></i>
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

<script>
// Initialize star rating input
document.querySelectorAll('.star-rating-input .star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        const container = this.parentElement;
        container.dataset.rating = rating;
        document.getElementById('ratingInput').value = rating;
        
        container.querySelectorAll('.star').forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far');
                s.classList.add('fas', 'text-warning');
            } else {
                s.classList.add('far');
                s.classList.remove('fas', 'text-warning');
            }
        });
    });
});

// Character counter
document.getElementById('reviewText')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Submit review with AJAX
function submitReview() {
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);
    
    if (formData.get('rating') == '0') {
        alert('Please select a rating');
        return;
    }
    
    if (!formData.get('review_text').trim()) {
        alert('Please write a review');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#writeReviewModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    submitBtn.disabled = true;
    
    // Prepare data for AJAX call
    const reviewData = {
        book_id: formData.get('book_id'),
        rating: formData.get('rating'),
        review_text: formData.get('review_text')
    };
    
    // AJAX call to Tracy's review submission API
    $.ajax({
        url: '../backend/review_submit.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(reviewData),
        success: function(response) {
            alert('Review submitted successfully!');
            document.querySelector('[data-bs-dismiss="modal"]').click();
            // Refresh page to show new review
            window.location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Review submission failed:', error);
            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to submit review. Please try again.';
            alert(errorMsg);
        },
        complete: function() {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Add to favorites with AJAX
function addToFavorites(bookId) {
    // Find the favorites button
    const favButton = document.querySelector('.btn-primary.btn-lg');
    const originalText = favButton.innerHTML;
    
    // Show loading state
    favButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
    favButton.disabled = true;
    
    // AJAX call to Tracy's favorites API
    $.ajax({
        url: '../backend/api/favorites.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({book_id: bookId}),
        success: function(response) {
            if (response.success) {
                alert('Added to favorites successfully!');
                // Update button to show it's favorited
                favButton.innerHTML = '<i class="fas fa-heart me-2"></i>Added to Favorites';
                favButton.classList.remove('btn-primary');
                favButton.classList.add('btn-success');
                favButton.disabled = true;
            } else {
                alert(response.message || 'Failed to add to favorites');
            }
        },
        error: function(xhr, status, error) {
            console.error('Favorites request failed:', error);
            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to add to favorites. Please try again.';
            alert(errorMsg);
            
            // Reset button on error
            favButton.innerHTML = originalText;
            favButton.disabled = false;
        }
    });
}

// Share book
function shareBook() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($book['title']); ?>',
            text: 'Check out this book!',
            url: window.location.href
        });
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}

// Toggle description
function toggleDescription() {
    const desc = document.getElementById('bookDescription');
    desc.classList.toggle('expanded');
}
</script>

<style>
.book-detail-cover {
    max-height: 600px;
    width: 100%;
    object-fit: cover;
}

.book-description {
    max-height: 200px;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.book-description.expanded {
    max-height: none;
}

.star-rating-input .star {
    font-size: 1.5rem;
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
}

.star-rating-input .star:hover {
    color: #ffc107;
}
</style>

<?php include 'includes/footer.php'; ?>
