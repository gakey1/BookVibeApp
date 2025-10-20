<?php
// index.php - Homepage for Book Review Website
// This tests PHP is working. Add DB later.
require 'config/db.php';

$pageTitle = "Welcome to Book Review Website";
$message = "Back-end setup by Tracy Nguyen. Front-end by Yvonne Gitonga.";
$stmt = $pdo->query("SELECT * FROM books ORDER BY RAND() LIMIT 5");
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap CSS CDN for quick styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center text-primary"><?php echo $pageTitle; ?></h1>
        <p class="lead text-center"><?php echo $message; ?></p>
        
        <!-- test Boostrap code -->
        <div class="row mt-5">
            <?php foreach ($books as $book): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/<?= $book['cover_image'] ?>" class="card-img-top" alt="<?= $book['title'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $book['title'] ?></h5>
                            <p class="card-text">By <?= $book['author'] ?></p>
                            <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Project Status</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"> Folders ready: backend, frontend, etc.</li>
                            <li class="list-group-item"> GitHub repo set up</li>
                            <li class="list-group-item"> Next: Database connection</li>
                            <li class="list-group-item"> Then: Add books/reviews</li>
                        </ul>
                        <p class="card-text mt-3">PHP is working! Version: <?php echo phpversion(); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <footer class="text-center mt-5 p-3 bg-white">
            <small>Local server: XAMPP | URL:       http://localhost/adv_web/14733_14783/book-review-website/</small>
        </footer>
    </div>
</body>
</html>