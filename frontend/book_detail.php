<?php 
// Define app constant for config access
define('BOOK_REVIEW_APP', true);

// Include header
include 'includes/header.php';

// Get book ID from URL
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Static book data using existing images
$books = [
    1 => ['id' => 1, 'title' => '1984', 'author' => 'George Orwell', 'cover' => '1984.jpg', 'rating' => 4.5, 'reviews' => 2847, 'genre' => 'Dystopian Fiction', 'year' => 1949, 'pages' => 328, 'isbn' => '978-0-452-28423-4', 'publisher' => 'Penguin Classics', 'description' => 'A dystopian social science fiction novel that follows the life of Winston Smith, a low-ranking member of "the Party", who is frustrated by the omnipresent eyes of the party, and its ominous ruler Big Brother. Winston Smith works in the Ministry of Truth where he rewrites historical records to conform to the state\'s ever-changing version of history.'],
    2 => ['id' => 2, 'title' => 'Atomic Habits', 'author' => 'James Clear', 'cover' => 'atomic_habits.jpg', 'rating' => 4.8, 'reviews' => 3214, 'genre' => 'Self-Help', 'year' => 2018, 'pages' => 320, 'isbn' => '978-0-7352-1129-2', 'publisher' => 'Avery', 'description' => 'An Easy & Proven Way to Build Good Habits & Break Bad Ones. No matter your goals, Atomic Habits offers a proven framework for improving--every day. James Clear, one of the world\'s leading experts on habit formation, reveals practical strategies that will teach you exactly how to form good habits, break bad ones, and master the tiny behaviors that lead to remarkable results.'],
    3 => ['id' => 3, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'cover' => 'gatsby.jpg', 'rating' => 4.2, 'reviews' => 1892, 'genre' => 'Classic Literature', 'year' => 1925, 'pages' => 180, 'isbn' => '978-0-7432-7356-5', 'publisher' => 'Scribner', 'description' => 'The story of the mysteriously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan, of lavish parties on Long Island at a time when The New York Times noted "gin was the national drink and sex the national obsession," it is an exquisitely crafted tale of America in the 1920s.'],
    4 => ['id' => 4, 'title' => 'Gone Girl', 'author' => 'Gillian Flynn', 'cover' => 'gone_girl.jpg', 'rating' => 4.3, 'reviews' => 2156, 'genre' => 'Psychological Thriller', 'year' => 2012, 'pages' => 419, 'isbn' => '978-0-307-58836-4', 'publisher' => 'Crown Publishers', 'description' => 'On a warm summer morning in North Carthage, Missouri, it is Nick and Amy Dunne\'s fifth wedding anniversary. Presents are being wrapped and reservations are being made when Nick\'s clever and beautiful wife disappears. Husband-of-the-year Nick isn\'t doing himself any favors with cringe-worthy daydreams about the slope and shape of his wife\'s head.'],
    5 => ['id' => 5, 'title' => 'Little Women', 'author' => 'Louisa May Alcott', 'cover' => 'little_women.jpg', 'rating' => 4.1, 'reviews' => 1678, 'genre' => 'Coming-of-Age', 'year' => 1868, 'pages' => 449, 'isbn' => '978-0-14-143960-1', 'publisher' => 'Penguin Classics', 'description' => 'Four sisters--Meg, Jo, Beth, and Amy March--detail their lives growing up during the Civil War. Despite harsh times, they cling to optimism, often finding themselves getting into trouble and learning the hard way. Their father is away at war, and they rely on their mother, fondly dubbed Marmee, to raise them.']
];

// Get book or default to first book
$book = $books[$bookId] ?? $books[1];
$book['cover'] = 'assets/images/books/' . $book['cover'];

// Static reviews data
$reviews = [
    ['user_name' => 'Sarah Johnson', 'avatar' => 'https://via.placeholder.com/50', 'rating' => 5, 'date' => '2 days ago', 'review_text' => 'Absolutely mind-blowing! This book changed my perspective completely. The writing is brilliant and the story stays with you long after you finish reading.'],
    ['user_name' => 'Mike Chen', 'avatar' => 'https://via.placeholder.com/50', 'rating' => 4, 'date' => '1 week ago', 'review_text' => 'Really enjoyed this one. Great character development and an engaging plot. Would definitely recommend to others.'],
    ['user_name' => 'Emma Wilson', 'avatar' => 'https://via.placeholder.com/50', 'rating' => 5, 'date' => '2 weeks ago', 'review_text' => 'One of my all-time favorites! The author has such a unique voice and the themes are so relevant to today\'s world.']
];

// Static ratings breakdown
$ratingsBreakdown = [5 => 45, 4 => 30, 3 => 15, 2 => 7, 1 => 3];

$pageTitle = htmlspecialchars($book['title']) . ' - Book Review Website';
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
                <button class="btn btn-primary btn-lg" onclick="addToFavorites(<?php echo $book['id']; ?>)">
                    <i class="fas fa-heart me-2"></i>Add to Favorites
                </button>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg">
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
                        <?php if ($book['year']): ?>
                        <tr>
                            <td class="text-muted">Year:</td>
                            <td><?php echo htmlspecialchars($book['year']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($book['pages'] && $book['pages'] > 0): ?>
                        <tr>
                            <td class="text-muted">Pages:</td>
                            <td><?php echo number_format($book['pages']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Genre:</td>
                            <td>
                                <a href="browse.php?genre=<?php echo urlencode(strtolower(str_replace(' ', '-', $book['genre']))); ?>" 
                                   class="badge bg-secondary text-decoration-none">
                                    <?php echo htmlspecialchars($book['genre']); ?>
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
                                    <span class="text-muted ms-2"><?php echo $review['date']; ?></span>
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
                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                    
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

// Submit review (placeholder functionality)
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
    
    // Static demo - just show success message
    alert('Review submitted successfully! (Demo mode - review not actually saved)');
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Add to favorites (placeholder functionality)
function addToFavorites(bookId) {
    alert('Added to favorites! (Demo mode - not actually saved)');
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