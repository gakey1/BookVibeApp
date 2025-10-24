<?php
session_start();
require '../config/db.php'; 

//validate book_id and fetch data
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Book ID not specified.");
}

$book_id = (int)$_GET['id']; // convert to integer for safety

//fetch book info
try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        die("Book not found.");
    }
} catch (PDOException $e) {
    die("Error fetching book: " . $e->getMessage());
}

//fetch reviews for this book
try {
    $stmt = $pdo->prepare("
        SELECT r.*, u.username 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.book_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$book_id]);
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching reviews: " . $e->getMessage());
}
?>

<!-- display book info -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($book['title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1><?= htmlspecialchars($book['title']) ?></h1>
    <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
    <p><strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></p>
    <img src="assets/images/<?= htmlspecialchars($book['cover_image']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($book['title']) ?>">
    <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
    
    <!-- display existing reviews -->
    <h3 class="mt-5">Reviews</h3>

<?php if (count($reviews) > 0): ?>
    <?php foreach ($reviews as $review): ?>
        <div class="card mb-3">
            <div class="card-body">
                <strong><?= htmlspecialchars($review['username']) ?></strong> 
                rated <?= $review['rating'] ?>/5
                <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                <small class="text-muted"><?= $review['created_at'] ?></small>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No reviews yet. Be the first to review!</p>
<?php endif; ?>

    <!-- add review form only for logged- in users -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <h4 class="mt-5">Add Your Review</h4>
    <form action="../backend/review_submit.php" method="post">
        <input type="hidden" name="book_id" value="<?= $book_id ?>">
        <div class="mb-3">
            <label>Rating (1-5)</label>
            <input type="number" name="rating" min="1" max="5" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Comment</label>
            <textarea name="comment" class="form-control" rows="3" required></textarea>
        </div>
        <button class="btn btn-primary">Submit Review</button>
    </form>
<?php else: ?>
    <p class="mt-3">Please <a href="../backend/login.php">login</a> to add a review.</p>
<?php endif; ?>
</div>
</body>
</html>
