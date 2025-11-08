// Account page JavaScript functionality

// Save profile changes
function saveProfile() {
    // TODO: Send AJAX request to Tracy's profile update API
    if (typeof showNotification === 'function') {
        showNotification('Profile update feature coming soon!', 'info');
    } else if (typeof showComingSoon === 'function') {
        showComingSoon(null, 'Profile Update');
    } else {
        alert('Profile update feature coming soon!');
    }
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Edit profile form
document.getElementById('editProfileForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // TODO: Send AJAX request to Tracy's profile update API
    if (typeof showNotification === 'function') {
        showNotification('Profile update feature coming soon!', 'info');
    } else if (typeof showComingSoon === 'function') {
        showComingSoon(null, 'Profile Update');
    } else {
        alert('Profile update feature coming soon!');
    }
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Change password form
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        if (typeof showNotification === 'function') {
            showNotification('New passwords do not match!', 'error');
        } else {
            alert('New passwords do not match!');
        }
        return;
    }
    
    // TODO: Send AJAX request to Tracy's password change API
    if (typeof showNotification === 'function') {
        showNotification('Password change feature coming soon!', 'info');
    } else if (typeof showComingSoon === 'function') {
        showComingSoon(null, 'Password Change');
    } else {
        alert('Password change feature coming soon!');
    }
    this.reset();
});

// Settings checkboxes
document.querySelectorAll('.form-check-input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // TODO: Send AJAX request to Tracy's settings API
        console.log(`Setting ${this.id} ready for API integration:`, this.checked);
    });
});