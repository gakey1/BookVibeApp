<?php
// Frontend login page - presentation layer only
session_start();

// Handle the backend authentication logic
require '../backend/login_handler.php';

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
    <title>Login - BookVibe</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Page-specific CSS -->
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Brand Section -->
        <div class="brand-section">
            <div class="brand-content">
                <!-- Logo -->
                <div class="logo">
                    <i class="fas fa-book"></i>
                    <span>BookVibe</span>
                </div>
                
                <h1 class="brand-title">Welcome Back</h1>
                <p class="brand-description">Sign in to your account to continue exploring your favorite books and reviews.</p>
                
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
                    <h2>Sign In</h2>
                    <p>Enter your credentials to access your account</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter your full name" required>
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <a href="#" onclick="showComingSoon(event)" style="color: var(--primary-purple); text-decoration: none;">Forgot Password?</a>
                    </div>
                    
                    <!-- Sign In Button -->
                    <button type="submit" class="btn-primary">Sign In</button>
                </form>
                
                <div class="footer-text">
                    Don't have an account? <a href="register.php">Create account</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Coming Soon Modal -->
    <div class="modal fade" id="comingSoonModal" tabindex="-1" aria-labelledby="comingSoonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="comingSoonModalLabel" style="color: var(--primary-purple);">Coming Soon!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-rocket fa-3x mb-3" style="color: var(--primary-purple);"></i>
                    <h4>Exciting Feature in Development</h4>
                    <p class="text-muted mb-4">This feature is currently being developed and will be available soon. Stay tuned for updates!</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-secondary">In Development</span>
                        <span class="badge" style="background: var(--primary-purple);">Coming Soon</span>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn" style="background: var(--primary-purple); color: white;" data-bs-dismiss="modal">Got it!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password visibility toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Coming Soon modal
        function showComingSoon(event) {
            event.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
            modal.show();
        }
    </script>
</body>
</html>