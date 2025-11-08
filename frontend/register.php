<?php
// Define app constant for includes access
define('BOOKVIBE_APP', true);

session_start();

// Handle the backend registration logic
require '../backend/register_handler.php';

// Get decorative books for the auth page - using hardcoded books for reliability
$decorativeBooks = [
    [
        'title' => '1984',
        'author' => 'George Orwell',
        'cover' => '1984.jpg'
    ],
    [
        'title' => 'The Great Gatsby', 
        'author' => 'F. Scott Fitzgerald',
        'cover' => 'gatsby.jpg'
    ],
    [
        'title' => 'Atomic Habits',
        'author' => 'James Clear', 
        'cover' => 'atomic_habits.jpg'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BookVibe</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Page-specific CSS -->
    <link rel="stylesheet" href="assets/css/register.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Brand Section -->
        <div class="brand-section">
            <div class="brand-content">
                <div class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>BookVibe</span>
                </div>
                
                <h1 class="brand-title">Join BookVibe</h1>
                <p class="brand-description">Your ultimate destination for discovering, reviewing, and sharing your love for books.</p>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <h4>Discover New Books</h4>
                            <p>Explore curated collections and trending titles</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h4>Share Reviews</h4>
                            <p>Rate and review books you've read</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div>
                            <h4>Build Your Library</h4>
                            <p>Create your personal collection of favorites</p>
                        </div>
                    </div>
                </div>
                
                <!-- Decorative Book Images -->
                <div class="decorative-books">
                    <?php foreach ($decorativeBooks as $book): ?>
                    <div class="book-preview">
                        <img src="assets/images/books/<?php echo htmlspecialchars($book['cover']); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Explore Button -->
                <div class="explore-section">
                    <a href="browse.php" class="btn btn-light btn-lg">
                        Explore Books
                    </a>
                </div>
            </div>
            
            <div class="footer-info">
                <p style="opacity: 0.9;">© 2025 BookVibe. All rights reserved.</p>
            </div>
        </div>
        
        <!-- Right Side - Form Section -->
        <div class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Join our community of book lovers</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="Enter your full name"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email (Optional)</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="your@email.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Create a strong password"
                                   required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="far fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        Create Account
                    </button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Log in</a>
                </div>
                
                <div class="footer-text">
                    By creating an account, you agree to our Terms of Service and Privacy Policy
                </div>
            </div>
        </div>
    </div>
    
    <!-- Page-specific JavaScript -->
    <script src="assets/js/register.js?v=<?php echo time(); ?>"></script>
</body>
</html>