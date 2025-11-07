<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'My Reviews - BookVibe';
include 'includes/header.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header('Location: ../backend/login.php');
    exit;
}

// Database logic to fetch user's reviews
require_once __DIR__ . '/../config/db.php'; 
$db = Database::getInstance();

$user_id = $_SESSION['user_id'];

// Fetch user's reviews with book details
$sql_reviews = "
    SELECT 
        r.*,
        b.title,
        b.author,
        b.cover_image,
        g.name as genre_name
    FROM 
        reviews r
    JOIN 
        books b ON r.book_id = b.book_id
    LEFT JOIN 
        genres g ON b.genre_id = g.genre_id
    WHERE 
        r.user_id = ?
    ORDER BY 
        r.created_at DESC";

$myReviews = $db->fetchAll($sql_reviews, [$user_id]);

$totalReviews = count($myReviews);
$avgRating = 0;
$reviewsThisMonth = 0;

if ($totalReviews > 0) {
    $ratings = array_column($myReviews, 'rating');
    $avgRating = round(array_sum($ratings) / count($ratings), 1);
    
    // Count reviews this month
    $thisMonth = date('Y-m');
    foreach ($myReviews as $review) {
        if (date('Y-m', strtotime($review['created_at'])) === $thisMonth) {
            $reviewsThisMonth++;
        }
    }
}
?>

<div class="container my-5">
    <?php if (!$isLoggedIn): ?>
    <!-- Not logged in message -->
    <div class="text-center py-5">
        <i class="fas fa-star fa-4x text-muted mb-4"></i>
        <h2>Please Log In</h2>
        <p class="text-muted mb-4">You need to be logged in to view your reviews.</p>
        <a href="login.php" class="btn btn-primary">Log In</a>
    </div>
    <?php else: ?>
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-star text-warning me-2"></i>My Reviews</h1>
            <p class="text-muted">Share your thoughts about the books you've read</p>
        </div>
        <a href="browse.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Write New Review
        </a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary"><?php echo $totalReviews; ?></h4>
                    <small class="text-muted">Total Reviews</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning"><?php echo $avgRating; ?></h4>
                    <small class="text-muted">Avg Rating Given</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success"><?php echo $reviewsThisMonth; ?></h4>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info"><?php echo $totalReviews > 0 ? date('M Y', strtotime($myReviews[0]['created_at'])) : 'N/A'; ?></h4>
                    <small class="text-muted">Latest Review</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Sort -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Search your reviews..." id="searchReviews">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="sortReviews">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="rating-high">Highest Rated</option>
                <option value="rating-low">Lowest Rated</option>
                <option value="most-liked">Most Liked</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterVisibility">
                <option value="all">All Reviews</option>
                <option value="public">Public Only</option>
                <option value="private">Private Only</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="filterRating">
                <option value="all">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
            </select>
        </div>
    </div>

    <!-- Reviews Content -->
    <?php if (empty($myReviews)): ?>
    <!-- Empty State -->
    <div class="text-center py-5">
        <i class="fas fa-edit fa-4x text-muted mb-4"></i>
        <h3>No reviews yet</h3>
        <p class="text-muted mb-4">Start sharing your thoughts about the books you've read!</p>
        <a href="browse.php" class="btn btn-primary">
            <i class="fas fa-search me-2"></i>Find Books to Review
        </a>
    </div>
    <?php else: ?>
    
    <!-- Reviews List -->
    <div id="reviewsContainer" class="row g-4">
        <?php foreach ($myReviews as $review): ?>
        <div class="col-lg-4 col-md-6 review-item" data-visibility="<?php echo $review['is_public'] ? 'public' : 'private'; ?>" data-rating="<?php echo $review['rating']; ?>">
            <div class="card h-100 review-item">
                <div class="card-body">
                    <!-- Book Info -->
                    <div class="d-flex mb-3">
                        <img src="assets/images/books/<?php echo htmlspecialchars($review['cover_image']); ?>" alt="<?php echo htmlspecialchars($review['title']); ?>" 
                             class="me-3" style="width: 60px; height: 90px; object-fit: cover; border-radius: 4px;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="book_detail.php?id=<?php echo $review['book_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($review['title']); ?>
                                </a>
                            </h6>
                            <p class="text-muted mb-2 small">by <?php echo htmlspecialchars($review['author']); ?></p>
                            <div class="book-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Review Content -->
                    <div class="review-text mb-3">
                        <p class="text-truncate-2"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    </div>
                    
                    <!-- Review Meta -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></small>
                        <span class="badge <?php echo $review['is_public'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $review['is_public'] ? 'Public' : 'Private'; ?>
                        </span>
                    </div>
                    
                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-thumbs-up me-1"></i><?php echo $review['likes']; ?> helpful
                        </small>
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="editReview(<?php echo $review['review_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                <i class="fas fa-trash"></i>
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

<!-- Edit Review Modal -->
<div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editReviewForm">
                    <div class="mb-3">
                        <label class="form-label">Your Rating</label>
                        <div class="star-rating-input" data-rating="0">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star star" data-rating="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="editRatingInput" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editReviewText" class="form-label">Your Review</label>
                        <textarea class="form-control" id="editReviewText" name="review_text" 
                                  rows="6" maxlength="2000" required></textarea>
                        <div class="form-text">
                            <span id="editCharCount">0</span>/2000 characters
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editIsPublic">
                            <label class="form-check-label" for="editIsPublic">
                                Make this review public
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveReviewEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchReviews')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(item => {
        const title = item.querySelector('h5').textContent.toLowerCase();
        const author = item.querySelector('.text-muted').textContent.toLowerCase();
        const text = item.querySelector('.review-text').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm) || text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Filter by visibility
document.getElementById('filterVisibility')?.addEventListener('change', function() {
    const filter = this.value;
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(item => {
        const visibility = item.dataset.visibility;
        
        if (filter === 'all' || visibility === filter) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Filter by rating
document.getElementById('filterRating')?.addEventListener('change', function() {
    const rating = this.value;
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(item => {
        const itemRating = item.dataset.rating;
        
        if (rating === 'all' || itemRating === rating) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Sort functionality
document.getElementById('sortReviews')?.addEventListener('change', function() {
    const sortBy = this.value;
    // Static demo - would implement sorting logic here
    console.log('Sorting by:', sortBy);
});

// Edit review
function editReview(reviewId) {
    // TODO: Load actual review data via AJAX when Tracy creates edit API
    // For now, show modal with placeholder data
    document.getElementById('editReviewText').value = 'Loading review data...';
    document.getElementById('editRatingInput').value = '0';
    document.getElementById('editIsPublic').checked = false;
    
    // Update character count
    document.getElementById('editCharCount').textContent = document.getElementById('editReviewText').value.length;
    
    // Initialize star rating
    updateStarRating(0);
    
    const modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
    modal.show();
    
    // Note: Ready for integration with Tracy's edit review API
}

// Save review edit
function saveReviewEdit() {
    // TODO: Send AJAX request to Tracy's edit review API
    // Ready for integration when backend API is available
    alert('Review edit functionality ready for Tracy\'s API integration');
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Toggle visibility
function toggleVisibility(reviewId) {
    // TODO: Send AJAX request to Tracy's toggle visibility API
    // Ready for integration when backend API is available
    alert('Visibility toggle ready for Tracy\'s API integration');
}

// Delete review
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        // TODO: Send AJAX request to Tracy's delete review API
        // Ready for integration when backend API is available
        alert('Delete functionality ready for Tracy\'s API integration');
    }
}

// Star rating for edit modal
document.querySelectorAll('.star-rating-input .star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        updateStarRating(rating);
    });
});

function updateStarRating(rating) {
    const container = document.querySelector('.star-rating-input');
    container.dataset.rating = rating;
    document.getElementById('editRatingInput').value = rating;
    
    container.querySelectorAll('.star').forEach((s, index) => {
        if (index < rating) {
            s.classList.remove('far');
            s.classList.add('fas', 'text-warning');
        } else {
            s.classList.add('far');
            s.classList.remove('fas', 'text-warning');
        }
    });
}

// Character counter for edit modal
document.getElementById('editReviewText')?.addEventListener('input', function() {
    document.getElementById('editCharCount').textContent = this.value.length;
});
</script>

<style>
.review-item .card {
    transition: transform 0.2s ease;
    border: none;
    box-shadow: var(--shadow-sm);
}

.review-item .card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
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

@media (max-width: 768px) {
    .review-item {
        margin-bottom: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
