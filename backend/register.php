<?php
// Define app constant for includes access
define('BOOKVIBE_APP', true);

session_start();
require '../config/db.php';
require '../includes/book_diversity.php';

// Get decorative books for the auth page
$decorativeBooks = [];
try {
    $diversityManager = BookDiversityManager::getInstance();
    $decorativeBooks = $diversityManager->getDecorativeBooks(3);
} catch (Exception $e) {
    // Fallback to empty array if database fails
    $decorativeBooks = [];
} 

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name_input = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($full_name_input === '' || $password === '') {
        $message = "Name and password are required.";
        $messageType = 'danger';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE full_name = ?");
        $stmt->execute([$full_name_input]);
        if ($stmt->fetch()) {
            $message = "Name already taken.";
            $messageType = 'danger';
        } else {
            // Insert user with hashed password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$full_name_input, $email, $password_hash]);
            $message = "Registration successful! You can now <a href='login.php' class='text-white'><u>login</u></a>.";
            $messageType = 'success';
        }
    }
}
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
    
    <style>
        /* Auth Page Styles */
        :root {
            --primary-purple: #7C3AED;
            --purple-dark: #5B21B6;
            --purple-light: #C4B5FD;
            --purple-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --text-dark: #1F2937;
            --text-medium: #6B7280;
            --bg-light: #F3F4F6;
            --border-color: #E5E7EB;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg-light);
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
        }
        
        .auth-left {
            flex: 1;
            background: var(--purple-gradient);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-right {
            flex: 1;
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-form {
            width: 100%;
            max-width: 400px;
        }
        
        .form-control-custom {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.2s;
        }
        
        .form-control-custom:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        .btn-primary-custom {
            background: var(--primary-purple);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary-custom:hover {
            background: var(--purple-dark);
            transform: translateY(-1px);
        }
        
        .text-purple {
            color: var(--primary-purple);
        }
        
        .auth-decorative-book {
            transition: transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .auth-decorative-book:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 767px) {
            .auth-container {
                flex-direction: column;
            }
            
            .auth-left {
                padding: 2rem;
                min-height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Brand Information -->
        <div class="auth-left">
            <div>
                <!-- Logo -->
                <div class="mb-4">
                    <a href="../frontend/index.php" class="text-white text-decoration-none">
                        <h2><i class="fas fa-book me-2"></i>BookVibe</h2>
                    </a>
                </div>
                
                <!-- Heading -->
                <h1 class="mb-3">Join BookVibe</h1>
                <p class="mb-4 fs-5">Create your account to start discovering, reviewing, and collecting your favorite books.</p>
                
                <!-- Features -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-search fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-1">Discover New Books</h5>
                            <p class="mb-0 opacity-75">Explore curated collections and trending titles</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-star fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-1">Share Reviews</h5>
                            <p class="mb-0 opacity-75">Rate and review books you've read</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-heart fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-1">Build Your Library</h5>
                            <p class="mb-0 opacity-75">Create your personal collection of favorites</p>
                        </div>
                    </div>
                </div>
                
                <!-- Decorative Books -->
                <div class="d-none d-lg-flex gap-3 mt-4">
                    <?php if (!empty($decorativeBooks)): ?>
                        <?php foreach ($decorativeBooks as $book): ?>
                            <img src="../frontend/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 class="rounded auth-decorative-book"
                                 style="width: 80px; height: 120px; object-fit: cover;"
                                 title="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback to placeholder images -->
                        <img src="https://via.placeholder.com/80x120/7C3AED/ffffff?text=📚" alt="Book" class="rounded">
                        <img src="https://via.placeholder.com/80x120/5B21B6/ffffff?text=📖" alt="Book" class="rounded">
                        <img src="https://via.placeholder.com/80x120/C4B5FD/ffffff?text=📕" alt="Book" class="rounded">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Authentication Form -->
        <div class="auth-right">
            <div class="auth-form">
                <div class="text-center mb-4">
                    <h3 class="mb-2">Create Account</h3>
                    <p class="text-muted">Join our community of book lovers today</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-custom" id="username" name="username" 
                               placeholder="Enter your full name" required>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-custom" id="email" name="email" 
                               placeholder="Enter your email (optional)">
                        <div class="form-text">Optional - used for account recovery</div>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control form-control-custom" id="password" name="password" 
                                   placeholder="Create a password" required minlength="6">
                            <button type="button" class="btn btn-link position-absolute end-0 top-0 h-100 pe-3" 
                                    onclick="togglePassword('password')">
                                <i class="fas fa-eye text-muted"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                I agree to the <a href="#" class="text-purple">Terms of Service</a> 
                                and <a href="#" class="text-purple">Privacy Policy</a>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Create Account Button -->
                    <button type="submit" class="btn btn-primary-custom w-100 mb-3">Create Account</button>
                </form>
                
                <!-- Sign In Link -->
                <div class="text-center">
                    <span class="text-muted">Already have an account? </span>
                    <a href="login.php" class="text-purple text-decoration-none">Sign in</a>
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
            const icon = input.nextElementSibling.querySelector('i');
            
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
    </script>
</body>
</html>
