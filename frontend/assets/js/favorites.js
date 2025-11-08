// Favorites page JavaScript functionality

// Search functionality
document.getElementById('searchFavorites')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const favorites = document.querySelectorAll('.favorite-item');
    
    favorites.forEach(item => {
        const title = item.querySelector('.book-title').textContent.toLowerCase();
        const author = item.querySelector('.book-author').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Sort functionality
document.getElementById('sortFavorites')?.addEventListener('change', function() {
    const sortBy = this.value;
    const container = document.getElementById('favoritesContainer');
    const items = Array.from(container.querySelectorAll('.favorite-item'));
    
    items.sort((a, b) => {
        switch(sortBy) {
            case 'recent':
                // Sort by date added (newest first) - using data-date if available
                return new Date(b.dataset.dateAdded || 0) - new Date(a.dataset.dateAdded || 0);
            case 'oldest':
                // Sort by date added (oldest first)
                return new Date(a.dataset.dateAdded || 0) - new Date(b.dataset.dateAdded || 0);
            case 'rating-high':
                // Sort by rating (highest first)
                const ratingA = parseFloat(a.querySelector('.book-rating').dataset.rating || 0);
                const ratingB = parseFloat(b.querySelector('.book-rating').dataset.rating || 0);
                return ratingB - ratingA;
            case 'rating-low':
                // Sort by rating (lowest first)
                const ratingA2 = parseFloat(a.querySelector('.book-rating').dataset.rating || 0);
                const ratingB2 = parseFloat(b.querySelector('.book-rating').dataset.rating || 0);
                return ratingA2 - ratingB2;
            case 'title-az':
                // Sort by title A-Z
                const titleA = a.querySelector('.book-title').textContent.toLowerCase();
                const titleB = b.querySelector('.book-title').textContent.toLowerCase();
                return titleA.localeCompare(titleB);
            case 'title-za':
                // Sort by title Z-A
                const titleA2 = a.querySelector('.book-title').textContent.toLowerCase();
                const titleB2 = b.querySelector('.book-title').textContent.toLowerCase();
                return titleB2.localeCompare(titleA2);
            default:
                return 0;
        }
    });
    
    // Clear container and re-add sorted items
    container.innerHTML = '';
    items.forEach(item => container.appendChild(item));
});

// Genre filter
document.getElementById('filterGenre')?.addEventListener('change', function() {
    const selectedGenre = this.value;
    const favorites = document.querySelectorAll('.favorite-item');
    
    favorites.forEach(item => {
        const genre = item.dataset.genre;
        
        if (!selectedGenre || genre === selectedGenre) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Toggle view
function toggleView(view) {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const container = document.getElementById('favoritesContainer');
    
    if (view === 'grid') {
        gridView.classList.add('active');
        listView.classList.remove('active');
        container.className = 'row';
        document.querySelectorAll('.favorite-item').forEach(item => {
            item.className = 'col-lg-4 col-md-6 mb-4 favorite-item';
        });
    } else {
        listView.classList.add('active');
        gridView.classList.remove('active');
        container.className = 'row';
        document.querySelectorAll('.favorite-item').forEach(item => {
            item.className = 'col-12 mb-3 favorite-item';
        });
    }
}

// Remove favorite with styled confirmation
function removeFavorite(bookId, favoriteId) {
    console.log('Custom removeFavorite called with:', bookId, favoriteId);
    // Show styled confirmation modal
    showRemoveConfirmation(bookId, favoriteId);
}

// Styled confirmation function
function showRemoveConfirmation(bookId, favoriteId) {
    const bookCard = document.getElementById('favorite-' + bookId);
    const bookTitle = bookCard.querySelector('.book-title').textContent;
    
    // Create confirmation modal
    const modalHtml = `
        <div class="modal fade" id="removeFavoriteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" style="color: var(--primary-purple);">Remove from Favorites</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-heart-broken fa-3x mb-3" style="color: #dc3545;"></i>
                        <h4>Remove "${bookTitle}"?</h4>
                        <p class="text-muted mb-4">Are you sure you want to remove this book from your favorites? You can always add it back later.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="confirmRemoveFavorite(${bookId}, ${favoriteId})" data-bs-dismiss="modal">
                            <i class="fas fa-trash me-2"></i>Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    const existingModal = document.getElementById('removeFavoriteModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM and show
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('removeFavoriteModal'));
    modal.show();
    
    // Clean up modal after it's hidden
    document.getElementById('removeFavoriteModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Actual remove function after confirmation
function confirmRemoveFavorite(bookId, favoriteId) {
    // Show loading state
    const bookCard = document.getElementById('favorite-' + bookId);
    const removeBtn = bookCard.querySelector('.remove-favorite');
    const originalHTML = removeBtn.innerHTML;
    
    removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    removeBtn.disabled = true;
    
    // Make AJAX call to backend API
    fetch('../backend/api/favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            book_id: parseInt(bookId),
            action: 'remove'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success - animate removal with smooth transition
            bookCard.style.transition = 'all 0.3s ease';
            bookCard.style.opacity = '0';
            bookCard.style.transform = 'scale(0.8)';
            bookCard.style.filter = 'grayscale(100%)';
            
            setTimeout(function() {
                bookCard.remove();
                
                // Update stats if needed
                const statsContainer = document.querySelector('.text-primary');
                if (statsContainer) {
                    const currentCount = parseInt(statsContainer.textContent);
                    if (currentCount > 1) {
                        statsContainer.textContent = currentCount - 1;
                    } else {
                        // Reload page if no more favorites
                        window.location.reload();
                    }
                }
                
                // Show success message with book title
                const bookTitle = bookCard.querySelector('.book-title').textContent;
                if (typeof showNotification === 'function') {
                    showNotification(bookTitle + ' removed from favorites', 'success');
                } else {
                    alert(bookTitle + ' has been removed from your favorites');
                }
            }, 300);
        } else {
            // Error - show styled notification and restore button
            if (typeof showNotification === 'function') {
                showNotification(data.message || 'Failed to remove favorite', 'error');
            } else {
                alert(data.message || 'Failed to remove favorite');
            }
            removeBtn.innerHTML = originalHTML;
            removeBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error removing favorite:', error);
        if (typeof showNotification === 'function') {
            showNotification('Failed to remove favorite. Please try again.', 'error');
        } else {
            alert('Failed to remove favorite. Please try again.');
        }
        removeBtn.innerHTML = originalHTML;
        removeBtn.disabled = false;
    });
}

// Export favorites function
function exportFavorites() {
    // Simple CSV export functionality
    const favoriteItems = document.querySelectorAll('.favorite-item:not([style*="display: none"])');
    let csvContent = 'Title,Author,Genre,Rating\n';
    
    favoriteItems.forEach(item => {
        const title = item.querySelector('.book-title').textContent;
        const author = item.querySelector('.book-author').textContent;
        const genre = item.querySelector('.genre-tag').textContent;
        const rating = item.querySelector('.rating-text').textContent;
        csvContent += `"${title}","${author}","${genre}","${rating}"\n`;
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'my-favorite-books.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Load more books function
function loadMoreFavorites() {
    // For now, all books are loaded at once from the database
    // TODO: In the future, implement pagination with LIMIT/OFFSET in SQL query
    // and AJAX call to load next batch of books
    const button = event.target;
    const container = document.getElementById('loadMoreContainer');
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
    button.disabled = true;
    
    // Simulate loading (in future, this would be an AJAX call)
    setTimeout(function() {
        // For now, just indicate all books are loaded
        button.innerHTML = '<i class="fas fa-check me-2"></i>All Books Loaded';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-secondary');
        
        // Optional: Hide the button after a delay
        setTimeout(function() {
            container.style.display = 'none';
        }, 2000);
    }, 500);
}