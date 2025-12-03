<?php 
// Define app constant for config access
define('BOOKVIBE_APP', true);

$pageTitle = 'My Account - BookVibe';
include 'includes/header.php';

require_once __DIR__ . '/../config/db.php'; 
$db = Database::getInstance();

// Check if user is logged in

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    // Redirect to login if not authenticated
    header('Location: login.php'); 
    exit;
}

$user_id = $_SESSION['user_id']; 

// Fetch User Data (Dynamic Replacement for the static $user array)
$sql_user = "
    SELECT 
        user_id, full_name, email, profile_picture, bio, created_at
    FROM 
        users
    WHERE 
        user_id = ?";
$user = $db->fetch($sql_user, [$user_id]);

if (!$user) {
    // User deleted or session invalid, destroy session
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch Dynamic Stats 
$sql_stats = "
    SELECT 
        COUNT(review_id) AS total_reviews,
        IFNULL(AVG(rating), 0) AS avg_rating_given
    FROM 
        reviews
    WHERE 
        user_id = ?";
$stats = $db->fetch($sql_stats, [$user_id]);

// Fetch Favourites Count
$user['total_reviews'] = $stats['total_reviews'];
$user['avg_rating_given'] = round($stats['avg_rating_given'], 1);
$user['total_favorites'] = $db->fetch("SELECT COUNT(favorite_id) AS count FROM favorites WHERE user_id = ?", [$user_id])['count'];

// Reading stats
$readingStats = [
    'books_read_this_year' => $user['total_reviews'], /* Using total reviews as a proxy */
    'pages_read_this_year' => 0,
    'reading_goal' => 50, // Default goal
    'favorite_genre' => 'N/A', 
    'reading_streak' => 0 
];

// Fetch last 10 user activities
$recentActivities = $db->fetchAll(
    "SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
    [$user_id]
);

?>

<div class="container my-5">
    <?php if (!$isLoggedIn): ?>
    <!-- Not logged in message -->
    <div class="text-center py-5">
        <i class="fas fa-user-circle fa-4x text-muted mb-4"></i>
        <h2>Please Log In</h2>
        <p class="text-muted mb-4">You need to be logged in to view your account.</p>
        <a href="login.php" class="btn btn-primary">Log In</a>
    </div>
    <?php else: ?>
    
    <!-- Account Header -->
    <div class="row mb-5">
        <div class="col-md-4 d-flex flex-column align-items-center text-center">
            <div class="profile-avatar mb-3 d-flex justify-content-center">
                <?php 
                $profileImage = 'assets/images/profiles/default.svg';
                if ($user['profile_picture'] && $user['profile_picture'] !== 'default.jpg' && $user['profile_picture'] !== 'default.svg') {
                    $profileImage = 'assets/images/profiles/' . $user['profile_picture'];
                }
                ?>
                <img src="<?php echo $profileImage; ?>" 
                     alt="Profile Picture" class="rounded-circle" width="150" height="150"
                     style="background: #f8f9fa; border: 2px solid #e9ecef; object-fit: cover;"
                     onerror="this.src='assets/images/profiles/default.svg'">
            </div>
            <h2 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="text-muted mb-1">@<?php echo htmlspecialchars($user['full_name']); ?></p>
            <p class="text-muted mb-3">Member since <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </button>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-star text-purple fa-2x mb-2"></i>
                            <h4><?php echo $user['total_reviews']; ?></h4>
                            <small class="text-muted">Reviews Written</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-heart text-purple-light fa-2x mb-2"></i>
                            <h4><?php echo $user['total_favorites']; ?></h4>
                            <small class="text-muted">Favorite Books</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">

                            <i class="fas fa-book text-purple-dark fa-2x mb-2"></i>
                            <h4><?php echo $user['total_reviews']; ?></h4>
                            <small class="text-muted">Books Read This Year</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">

                            <i class="fas fa-fire text-purple-gradient fa-2x mb-2"></i>
                            <h4><?php echo $user['total_reviews']; ?></h4>
                            <small class="text-muted">Day Reading Streak</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="accountTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" 
                    type="button" role="tab">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reading-tab" data-bs-toggle="tab" data-bs-target="#reading" 
                    type="button" role="tab">Reading Progress</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" 
                    type="button" role="tab">My Reviews</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" 
                    type="button" role="tab">Settings</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="accountTabContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line me-2"></i>Reading Progress</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>2024 Reading Goal</span>
                                    <span><?php echo $readingStats['books_read_this_year']; ?> / <?php echo $readingStats['reading_goal']; ?> books</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo ($readingStats['books_read_this_year'] / $readingStats['reading_goal']) * 100; ?>%">
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted">You've read <?php echo number_format($readingStats['pages_read_this_year']); ?> pages this year!</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-clock text-purple me-2"></i>Recent Activity</h5>
                        </div>
                        <!--
                        <div class="card-body">
                            <div class="activity-item mb-3">
                                <i class="fas fa-star text-purple me-2"></i>
                                <span>Reviewed <strong>1984</strong> - 5 stars</span>
                                <small class="text-muted ms-2">2 days ago</small>
                            </div>
                            <div class="activity-item mb-3">
                                <i class="fas fa-heart text-purple-light me-2"></i>
                                <span>Added <strong>The Great Gatsby</strong> to favorites</span>
                                <small class="text-muted ms-2">5 days ago</small>
                            </div>
                            <div class="activity-item mb-3">
                                <i class="fas fa-book text-purple-dark me-2"></i>
                                <span>Finished reading <strong>Atomic Habits</strong></span>
                                <small class="text-muted ms-2">1 week ago</small>
                            </div>
                        </div>
                    -->
                        
                        <div class="card-body">
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="activity-item mb-3">
                                    <?php
                                        switch($activity['activity_type']) {
                                            case 'review': $icon = 'fas fa-star text-purple'; break;
                                            case 'favorite_add': $icon = 'fas fa-heart text-purple-light'; break;
                                            case 'favorite_remove': $icon = 'fas fa-heart-broken text-danger'; break;
                                            case 'finished_reading': $icon = 'fas fa-book text-purple-dark'; break;
                                            default: $icon = 'fas fa-info-circle'; 
                                        }
                                    ?>
                                    <i class="<?php echo $icon; ?> me-2"></i>
                                    <span><?php echo htmlspecialchars($activity['description']); ?></span>
                                    <small class="text-muted ms-2"><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent activity yet.</p>
                        <?php endif; ?>
                        </div>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-user me-2"></i>About Me</h5>
                        </div>
                        <div class="card-body">

                            <p><?php echo $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : '<em class="text-muted">No bio added yet.</em>'; ?></p>
    
                            <?php 
                            // Check if location exists
                            if (isset($user['location']) && !empty($user['location'])): 
                            ?>
                            <p>
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($user['location']); ?>
                            </p>
                            <?php endif; ?>

                            <?php 

                            if (isset($user['website']) && !empty($user['website'])): 
                            ?>
                            <p>
                                <i class="fas fa-globe me-2"></i>
                                <a href="<?php echo htmlspecialchars($user['website']); ?>" target="_blank">Website</a>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie me-2"></i>Reading Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-item mb-2">
                                <span>Favorite Genre:</span>
                                <strong><?php echo htmlspecialchars($readingStats['favorite_genre']); ?></strong>
                            </div>
                            <div class="stat-item mb-2">
                                <span>Average Rating:</span>
                                <strong><?php echo $user['avg_rating_given']; ?> stars</strong>
                            </div>
                            <div class="stat-item">
                                <span>Reading Streak:</span>
                                <strong><?php echo $readingStats['reading_streak']; ?> days</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reading Progress Tab -->
        <div class="tab-pane fade" id="reading" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book-open me-2"></i>My Reading Journey</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary"><?php echo $readingStats['books_read_this_year']; ?></h3>
                                <small>Books Read</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success"><?php echo number_format($readingStats['pages_read_this_year']); ?></h3>
                                <small>Pages Read</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning"><?php echo $readingStats['reading_goal']; ?></h3>
                                <small>2024 Goal</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info"><?php echo round(($readingStats['books_read_this_year'] / $readingStats['reading_goal']) * 100); ?>%</h3>
                                <small>Goal Progress</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Monthly Reading Progress</h6>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 80%"></div>
                        </div>
                        <small class="text-muted">You're on track to meet your reading goal!</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Reading Tip:</strong> Try setting aside 30 minutes each day for reading to maintain your streak!
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div class="tab-pane fade" id="reviews" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-star me-2"></i>My Reviews</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">You have written <?php echo $user['total_reviews']; ?> reviews.</p>
                    <a href="reviews.php" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View All My Reviews
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-bell me-2"></i>Notifications</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    Email notifications for new reviews
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="reviewReminders" checked>
                                <label class="form-check-label" for="reviewReminders">
                                    Reading goal reminders
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="recommendations">
                                <label class="form-check-label" for="recommendations">
                                    Book recommendations
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-shield-alt me-2"></i>Privacy</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="publicProfile" checked>
                                <label class="form-check-label" for="publicProfile">
                                    Make my profile public
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="showReviews" checked>
                                <label class="form-check-label" for="showReviews">
                                    Show my reviews publicly
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showFavorites">
                                <label class="form-check-label" for="showFavorites">
                                    Show my favorites list
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-key me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="changePasswordForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editFullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Username not available">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="editBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="editBio" rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="editLocation" value="<?php echo isset($user['location']) ? htmlspecialchars($user['location']) : ''; ?>" placeholder="Add your location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editWebsite" class="form-label">Website</label>
                            <input type="url" class="form-control" id="editWebsite" value="<?php echo isset($user['website']) ? htmlspecialchars($user['website']) : ''; ?>" placeholder="Add your website">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="assets/css/account.css?v=<?php echo time(); ?>">

<!-- Page-specific JavaScript -->
<script src="assets/js/account.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/footer.php'; ?>
