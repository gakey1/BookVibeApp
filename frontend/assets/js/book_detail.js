// Book detail page JavaScript functionality

// Initialize star rating input
document.querySelectorAll('.star-rating-input .star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.starValue;
        const container = this.parentElement;
        container.dataset.inputRating = rating;
        document.getElementById('ratingInput').value = rating;
        
        container.querySelectorAll('.star').forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far');
                s.classList.add('fas', 'text-warning');
            } else {
                s.classList.add('far');
                s.classList.remove('fas', 'text-warning');
            }
        });
    });
});

// Character counter
document.getElementById('reviewText')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Submit review with AJAX
function submitReview() {
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);
    
    if (formData.get('rating') == '0') {
        if (typeof showNotification === 'function') {
            showNotification('Please select a rating', 'warning');
        } else {
            alert('Please select a rating');
        }
        return;
    }
    
    if (!formData.get('review_text').trim()) {
        if (typeof showNotification === 'function') {
            showNotification('Please write a review', 'warning');
        } else {
            alert('Please write a review');
        }
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#writeReviewModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    submitBtn.disabled = true;
    
    // Prepare data for AJAX call
    const reviewData = {
        book_id: formData.get('book_id'),
        rating: formData.get('rating'),
        review_text: formData.get('review_text')
    };
    
    console.log('Submitting review data:', reviewData);
    
    // AJAX call to review submission API
    fetch('../backend/review_submit.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(reviewData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            if (typeof showNotification === 'function') {
                showNotification('Review submitted successfully!', 'success');
            } else {
                alert('Review submitted successfully!');
            }
            document.querySelector('[data-bs-dismiss="modal"]').click();
            // Refresh page to show new review
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to submit review');
        }
    })
    .catch(error => {
        console.error('Review submission failed:', error);
        
        let errorMsg = 'Failed to submit review. Please try again.';
        if (error.message) {
            if (error.message.includes('already reviewed')) {
                errorMsg = 'You have already reviewed this book. You can only submit one review per book.';
            } else {
                errorMsg = error.message;
            }
        }
        
        if (typeof showNotification === 'function') {
            showNotification(errorMsg, 'warning');
        } else {
            alert(errorMsg);
        }
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Toggle favorites functionality for book detail page
function toggleBookFavorite(button, bookId) {
    const isFavorited = button.classList.contains('favorited');
    const action = isFavorited ? 'remove' : 'add';
    const originalHTML = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + (action === 'add' ? 'Adding...' : 'Removing...');
    button.disabled = true;
    
    // Make AJAX request
    fetch('../backend/api/favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: parseInt(bookId),
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Toggle button state
            if (action === 'add') {
                button.classList.add('favorited');
                button.innerHTML = '<i class="fas fa-heart text-danger me-2"></i>Favorited';
            } else {
                button.classList.remove('favorited');
                button.innerHTML = '<i class="far fa-heart text-danger me-2"></i>Add to Favorites';
            }
            
            // Show success notification
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'success');
            } else {
                alert(data.message);
            }
        } else {
            // Show error and restore button
            if (typeof showNotification === 'function') {
                showNotification(data.message || 'Failed to update favorites', 'error');
            } else {
                alert(data.message || 'Failed to update favorites');
            }
            button.innerHTML = originalHTML;
        }
    })
    .catch(error => {
        console.error('Error updating favorite:', error);
        if (typeof showNotification === 'function') {
            showNotification('Failed to update favorites. Please try again.', 'error');
        } else {
            alert('Failed to update favorites. Please try again.');
        }
        button.innerHTML = originalHTML;
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Share book
function shareBook() {
    if (navigator.share) {
        navigator.share({
            title: document.querySelector('h1').textContent,
            text: 'Check out this book!',
            url: window.location.href
        });
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}

// Toggle description
function toggleDescription() {
    const desc = document.getElementById('bookDescription');
    desc.classList.toggle('expanded');
}