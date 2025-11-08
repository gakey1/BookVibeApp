/* Reviews Page JavaScript */

// Search functionality
document.getElementById('searchReviews')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(item => {
        const title = item.querySelector('h6').textContent.toLowerCase();
        const author = item.querySelector('.text-muted').textContent.toLowerCase();
        const text = item.querySelector('.review-text').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm) || text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Filter by visibility
document.getElementById('filterVisibility')?.addEventListener('change', function() {
    const filter = this.value;
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(item => {
        const visibility = item.dataset.visibility;
        
        if (filter === 'all' || visibility === filter) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Filter by rating
document.getElementById('filterRating')?.addEventListener('change', function() {
    const rating = this.value;
    const reviews = document.querySelectorAll('.review-item');
    
    reviews.forEach(item => {
        const itemRating = item.dataset.reviewRating;
        
        if (rating === 'all' || itemRating === rating) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Sort functionality
document.getElementById('sortReviews')?.addEventListener('change', function() {
    const sortBy = this.value;
    // Static demo - would implement sorting logic here
    console.log('Sorting by:', sortBy);
});

// Edit review
function editReview(reviewId) {
    // TODO: Load actual review data via AJAX when Tracy creates edit API
    // For now, show modal with placeholder data
    document.getElementById('editReviewText').value = 'Loading review data...';
    document.getElementById('editRatingInput').value = '0';
    document.getElementById('editIsPublic').checked = false;
    
    // Update character count
    document.getElementById('editCharCount').textContent = document.getElementById('editReviewText').value.length;
    
    // Initialize star rating
    updateStarRating(0);
    
    const modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
    modal.show();
    
    // Note: Ready for integration with Tracy's edit review API
}

// Save review edit
function saveReviewEdit() {
    // TODO: Send AJAX request to Tracy's edit review API
    // Ready for integration when backend API is available
    alert('Review edit functionality ready for Tracy\'s API integration');
    document.querySelector('[data-bs-dismiss="modal"]').click();
}

// Toggle visibility
function toggleVisibility(reviewId) {
    // TODO: Send AJAX request to Tracy's toggle visibility API
    // Ready for integration when backend API is available
    alert('Visibility toggle ready for Tracy\'s API integration');
}

// Delete review
let currentReviewToDelete = null;

function deleteReview(reviewId) {
    currentReviewToDelete = reviewId;
    const modal = new bootstrap.Modal(document.getElementById('deleteReviewModal'));
    modal.show();
}

// Handle delete confirmation
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (currentReviewToDelete) {
        // TODO: Send AJAX request to delete review API
        // Ready for integration when backend API is available
        alert('Delete functionality ready for API integration');
        
        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteReviewModal'));
        modal.hide();
        
        // Reset the review ID
        currentReviewToDelete = null;
    }
});

// Star rating for edit modal
document.querySelectorAll('.star-rating-input .star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.starValue;
        updateStarRating(rating);
    });
});

function updateStarRating(rating) {
    const container = document.querySelector('.star-rating-input');
    container.dataset.inputRating = rating;
    document.getElementById('editRatingInput').value = rating;
    
    container.querySelectorAll('.star').forEach((s, index) => {
        if (index < rating) {
            s.classList.remove('far');
            s.classList.add('fas', 'text-warning');
        } else {
            s.classList.add('far');
            s.classList.remove('fas', 'text-warning');
        }
    });
}

// Character counter for edit modal
document.getElementById('editReviewText')?.addEventListener('input', function() {
    document.getElementById('editCharCount').textContent = this.value.length;
});