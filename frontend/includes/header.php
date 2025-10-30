<?php
// Define app constant for config access if not already defined
if (!defined('BOOKVIBE_APP')) {
    define('BOOKVIBE_APP', true);
}

// Include config first (contains session configuration)
if (!class_exists('Database')) {
    require_once '../config/db.php';
}

// Start session if not already started (after config is loaded)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userAvatar = $_SESSION['user_avatar'] ?? 'assets/images/profiles/default.jpg';

// Check session timeout
if ($isLoggedIn && isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
    // Session expired
    session_destroy();
    $isLoggedIn = false;
    $userName = '';
    $userAvatar = 'assets/images/profiles/default.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'BookVibe - Discover Your Next Great Read'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i>BookVibe
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="browse.php">All Books</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="browse.php?genre=fiction">Fiction</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=romance">Romance</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=thriller">Thriller</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=sci-fi">Sci-Fi</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=fantasy">Fantasy</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=mystery">Mystery</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=non-fiction">Non-Fiction</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=biography">Biography</a></li>
                            <li><a class="dropdown-item" href="browse.php?genre=history">History</a></li>
                        </ul>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="favorites.php">My Favorites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account.php">My Account</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Search Bar -->
                <form class="d-flex search-bar me-3" role="search">
                    <div class="input-group">
                        <input type="search" class="form-control form-control-custom" id="searchInput" 
                               placeholder="Search books, authors, genres..." aria-label="Search">
                        <button class="btn btn-primary-custom" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <?php if ($isLoggedIn): ?>
                        <!-- Logged In User -->
                        <div class="dropdown">
                            <button class="btn btn-ghost dropdown-toggle d-flex align-items-center" 
                                    type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <img src="<?php echo $userAvatar; ?>" alt="Profile" 
                                     class="rounded-circle me-2" width="32" height="32">
                                <span class="d-none d-md-inline"><?php echo $userName; ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="account.php">
                                    <i class="fas fa-user me-2"></i>My Account
                                </a></li>
                                <li><a class="dropdown-item" href="reviews.php">
                                    <i class="fas fa-star me-2"></i>My Reviews
                                </a></li>
                                <li><a class="dropdown-item" href="favorites.php">
                                    <i class="fas fa-heart me-2"></i>My Favorites
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../backend/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Not Logged In -->
                        <a href="../backend/login.php" class="btn btn-ghost">Login</a>
                        <a href="../backend/register.php" class="btn btn-primary-custom ms-2">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>