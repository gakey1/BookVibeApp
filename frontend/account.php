<?php 
// Define app constant for config access
define('BOOK_REVIEW_APP', true);

$pageTitle = 'My Account - Book Review Website';
include 'includes/header.php';

// Static user data for demonstration
$user = [
    'id' => 1,
    'full_name' => 'Demo User',
    'email' => 'demo@example.com',
    'username' => 'demouser',
    'profile_picture' => null,
    'bio' => 'Book lover and avid reader. Always looking for the next great story!',
    'location' => 'New York, NY',
    'website' => 'https://example.com',
    'joined_date' => 'September 15, 2024',
    'total_reviews' => 12,
    'total_favorites' => 3,
    'avg_rating_given' => 4.2
];

// Static reading stats
$readingStats = [
    'books_read_this_year' => 24,
    'pages_read_this_year' => 7842,
    'reading_goal' => 30,
    'favorite_genre' => 'Classic Literature',
    'reading_streak' => 15
];

// Check if user is logged in (simulate login for demo)
$isLoggedIn = isset($_SESSION['user_id']) || true; // Set to true for demo purposes
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
        <div class="col-md-4 text-center">
            <div class="profile-avatar mb-3">
                <?php if ($user['profile_picture']): ?>
                <img src="assets/images/profiles/<?php echo $user['profile_picture']; ?>" 
                     alt="Profile Picture" class="rounded-circle img-fluid" width="150" height="150">
                <?php else: ?>
                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                     style="width: 150px; height: 150px; font-size: 3rem; color: white;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                </div>
                <?php endif; ?>
            </div>
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
            <p class="text-muted">Member since <?php echo $user['joined_date']; ?></p>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </button>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-star text-warning fa-2x mb-2"></i>
                            <h4><?php echo $user['total_reviews']; ?></h4>
                            <small class="text-muted">Reviews Written</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-heart text-danger fa-2x mb-2"></i>
                            <h4><?php echo $user['total_favorites']; ?></h4>
                            <small class="text-muted">Favorite Books</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-book text-success fa-2x mb-2"></i>
                            <h4><?php echo $readingStats['books_read_this_year']; ?></h4>
                            <small class="text-muted">Books Read This Year</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-fire text-orange fa-2x mb-2"></i>
                            <h4><?php echo $readingStats['reading_streak']; ?></h4>
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
                            <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-item mb-3">
                                <i class="fas fa-star text-warning me-2"></i>
                                <span>Reviewed <strong>1984</strong> - 5 stars</span>
                                <small class="text-muted ms-2">2 days ago</small>
                            </div>
                            <div class="activity-item mb-3">
                                <i class="fas fa-heart text-danger me-2"></i>
                                <span>Added <strong>The Great Gatsby</strong> to favorites</span>
                                <small class="text-muted ms-2">5 days ago</small>
                            </div>
                            <div class="activity-item mb-3">
                                <i class="fas fa-book text-success me-2"></i>
                                <span>Finished reading <strong>Atomic Habits</strong></span>
                                <small class="text-muted ms-2">1 week ago</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-user me-2"></i>About Me</h5>
                        </div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                            <?php if ($user['location']): ?>
                            <p><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($user['location']); ?></p>
                            <?php endif; ?>
                            <?php if ($user['website']): ?>
                            <p><i class="fas fa-globe me-2"></i><a href="<?php echo htmlspecialchars($user['website']); ?>" target="_blank">Website</a></p>
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
                            <input type="text" class="form-control" id="editUsername" value="<?php echo htmlspecialchars($user['username']); ?>">
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
                            <input type="text" class="form-control" id="editLocation" value="<?php echo htmlspecialchars($user['location']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editWebsite" class="form-label">Website</label>
                            <input type="url" class="form-control" id="editWebsite" value="<?php echo htmlspecialchars($user['website']); ?>">
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

<script>
// Save profile changes
function saveProfile() {
    // Static demo - would make API call here
    alert('Profile updated successfully! (Demo mode - changes not actually saved)');
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Change password form
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return;
    }
    
    // Static demo - would make API call here
    alert('Password updated successfully! (Demo mode - not actually changed)');
    this.reset();
});

// Settings checkboxes
document.querySelectorAll('.form-check-input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // Static demo - would save settings here
        console.log(`Setting ${this.id} changed to:`, this.checked);
    });
});
</script>

<style>
.activity-item {
    border-left: 3px solid #e9ecef;
    padding-left: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.text-orange {
    color: #fd7e14 !important;
}
</style>

<?php include 'includes/footer.php'; ?>
