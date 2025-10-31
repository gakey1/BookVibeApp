<?php 
// Define app constant for config access
define('BOOK_REVIEW_APP', true);

$pageTitle = 'My Reviews - Book Review Website';
include 'includes/header.php';

// Static reviews data
$myReviews = [
    [
        'id' => 1,
        'book_id' => 1,
        'book_title' => '1984',
        'book_author' => 'George Orwell',
        'book_cover' => 'assets/images/books/1984.jpg',
        'rating' => 5,
        'review_text' => 'Absolutely mind-blowing dystopian masterpiece! Orwell\'s vision of a totalitarian society is both terrifying and brilliant. The concepts of Big Brother, doublethink, and the Thought Police are as relevant today as they were when this was written. Winston Smith\'s journey is heartbreaking and the ending left me speechless. A must-read for everyone.',
        'created_at' => '2024-10-25',
        'is_public' => true,
        'likes' => 23,
        'helpful_votes' => 18
    ],
    [
        'id' => 2,
        'book_id' => 2,
        'book_title' => 'Atomic Habits',
        'book_author' => 'James Clear',
        'book_cover' => 'assets/images/books/atomic_habits.jpg',
        'rating' => 5,
        'review_text' => 'This book completely changed how I think about building habits. Clear\'s approach is practical and scientifically backed. The 1% better every day concept is so simple yet powerful. I\'ve already implemented several strategies from this book and seen real results. Highly recommended for anyone looking to improve their life through better habits.',
        'created_at' => '2024-10-20',
        'is_public' => true,
        'likes' => 15,
        'helpful_votes' => 12
    ],
    [
        'id' => 3,
        'book_id' => 3,
        'book_title' => 'The Great Gatsby',
        'book_author' => 'F. Scott Fitzgerald',
        'book_cover' => 'assets/images/books/gatsby.jpg',
        'rating' => 4,
        'review_text' => 'A beautiful exploration of the American Dream and its illusions. Fitzgerald\'s prose is absolutely stunning - every sentence feels like poetry. The symbolism throughout the novel is masterful, especially the green light. While I found some parts slow, the overall impact is undeniable. Nick Carraway is a fascinating narrator.',
        'created_at' => '2024-10-15',
        'is_public' => false,
        'likes' => 8,
        'helpful_votes' => 6
    ],
    [
        'id' => 4,
        'book_id' => 4,
        'book_title' => 'Gone Girl',
        'book_author' => 'Gillian Flynn',
        'book_cover' => 'assets/images/books/gone_girl.jpg',
        'rating' => 4,
        'review_text' => 'What a psychological thriller! Flynn creates two of the most unreliable narrators I\'ve ever encountered. The twist in the middle completely flipped my understanding of the story. The exploration of marriage and media manipulation is brilliant. Some parts felt a bit too dark for my taste, but the craftsmanship is undeniable.',
        'created_at' => '2024-10-10',
        'is_public' => true,
        'likes' => 31,
        'helpful_votes' => 25
    ]
];

// Check if user is logged in (simulate login for demo)
$isLoggedIn = isset($_SESSION['user_id']) || true; // Set to true for demo purposes

// Calculate stats
$totalReviews = count($myReviews);
$publicReviews = count(array_filter($myReviews, fn($r) => $r['is_public']));
$totalLikes = array_sum(array_column($myReviews, 'likes'));
$avgRating = $totalReviews > 0 ? array_sum(array_column($myReviews, 'rating')) / $totalReviews : 0;
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
                    <h4 class="text-success"><?php echo $publicReviews; ?></h4>
                    <small class="text-muted">Public Reviews</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning"><?php echo number_format($avgRating, 1); ?></h4>
                    <small class="text-muted">Avg Rating Given</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info"><?php echo $totalLikes; ?></h4>
                    <small class="text-muted">Total Likes</small>
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
    <div id="reviewsContainer">
        <?php foreach ($myReviews as $review): ?>
        <div class="review-item mb-4" data-visibility="<?php echo $review['is_public'] ? 'public' : 'private'; ?>" data-rating="<?php echo $review['rating']; ?>">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="<?php echo $review['book_cover']; ?>" alt="<?php echo htmlspecialchars($review['book_title']); ?>" 
                                 class="img-fluid rounded shadow-sm">
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1">
                                        <a href="book_detail.php?id=<?php echo $review['book_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($review['book_title']); ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted mb-2">by <?php echo htmlspecialchars($review['book_author']); ?></p>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="editReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-edit me-2"></i>Edit Review</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="toggleVisibility(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-<?php echo $review['is_public'] ? 'eye-slash' : 'eye'; ?> me-2"></i>
                                            Make <?php echo $review['is_public'] ? 'Private' : 'Public'; ?></a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteReview(<?php echo $review['id']; ?>)">
                                            <i class="fas fa-trash me-2"></i>Delete Review</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="rating me-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="badge <?php echo $review['is_public'] ? 'bg-success' : 'bg-secondary'; ?> me-2">
                                    <?php echo $review['is_public'] ? 'Public' : 'Private'; ?>
                                </span>
                                <small class="text-muted">Written on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                            
                            <div class="review-text mb-3">
                                <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="review-stats">
                                    <span class="me-3">
                                        <i class="fas fa-thumbs-up text-primary me-1"></i>
                                        <?php echo $review['likes']; ?> likes
                                    </span>
                                    <span>
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        <?php echo $review['helpful_votes']; ?> helpful
                                    </span>
                                </div>
                                <div>
                                    <a href="book_detail.php?id=<?php echo $review['book_id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Book
                                    </a>
                                </div>
                            </div>
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
    // Static demo - would load review data here
    document.getElementById('editReviewText').value = 'Sample review text for editing...';
    document.getElementById('editRatingInput').value = '4';
    document.getElementById('editIsPublic').checked = true;
    
    // Update character count
    document.getElementById('editCharCount').textContent = document.getElementById('editReviewText').value.length;
    
    // Initialize star rating
    updateStarRating(4);
    
    const modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
    modal.show();
}

// Save review edit
function saveReviewEdit() {
    // Static demo - would make API call here
    alert('Review updated successfully! (Demo mode - changes not actually saved)');
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Toggle visibility
function toggleVisibility(reviewId) {
    // Static demo - would make API call here
    alert('Review visibility updated! (Demo mode - not actually changed)');
}

// Delete review
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        // Static demo - would make API call here
        alert('Review deleted! (Demo mode - not actually deleted)');
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
.review-item {
    transition: transform 0.2s;
}

.review-item:hover {
    transform: translateY(-2px);
}

.review-text {
    max-height: 150px;
    overflow: hidden;
    position: relative;
}

.review-stats i {
    font-size: 0.875rem;
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
