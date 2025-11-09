/**
 * BookVibe API JavaScript Client
 * 
 * Handles all AJAX requests to backend APIs
 */

class BookVibeAPI {
    constructor() {
        this.baseURL = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
    }

    // Generic API request method
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}/api/${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    // Authentication APIs
    async login(email, password, rememberMe = false) {
        return this.request('auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ email, password, remember_me: rememberMe })
        });
    }

    async register(fullName, email, password, confirmPassword) {
        return this.request('auth.php?action=register', {
            method: 'POST',
            body: JSON.stringify({ 
                full_name: fullName, 
                email, 
                password, 
                confirm_password: confirmPassword 
            })
        });
    }

    async logout() {
        return this.request('auth.php?action=logout', {
            method: 'POST'
        });
    }

    async checkAuth() {
        return this.request('auth.php?action=check');
    }

    async getProfile() {
        return this.request('auth.php?action=profile');
    }

    async updateProfile(data) {
        return this.request('auth.php?action=profile', {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async changePassword(currentPassword, newPassword, confirmPassword) {
        return this.request('auth.php?action=change-password', {
            method: 'POST',
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            })
        });
    }

    // Books APIs
    async getBooks(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`books.php?action=list&${queryString}`);
    }

    async getBook(bookId) {
        return this.request(`books.php?action=detail&book_id=${bookId}`);
    }

    async searchBooks(query, params = {}) {
        const searchParams = new URLSearchParams({ q: query, ...params }).toString();
        return this.request(`books.php?action=search&${searchParams}`);
    }

    async getTrendingBooks(limit = 5) {
        return this.request(`books.php?action=trending&limit=${limit}`);
    }

    async getGenres() {
        return this.request('books.php?action=genres');
    }

    async getBooksByGenre(genreId, limit = 8) {
        return this.request(`books.php?action=by-genre&genre_id=${genreId}&limit=${limit}`);
    }

    async getRecentBooks(limit = 8) {
        return this.request(`books.php?action=recent&limit=${limit}`);
    }

    async getPopularBooks(limit = 8) {
        return this.request(`books.php?action=popular&limit=${limit}`);
    }

    async getRelatedBooks(bookId, limit = 4) {
        return this.request(`books.php?action=related&book_id=${bookId}&limit=${limit}`);
    }

    // Reviews APIs
    async createReview(bookId, rating, title, text, isPublic = true) {
        return this.request('reviews.php?action=create', {
            method: 'POST',
            body: JSON.stringify({
                book_id: bookId,
                rating,
                review_title: title,
                review_text: text,
                is_public: isPublic
            })
        });
    }

    async updateReview(reviewId, rating, title, text, isPublic = true) {
        return this.request('reviews.php?action=update', {
            method: 'PUT',
            body: JSON.stringify({
                review_id: reviewId,
                rating,
                review_title: title,
                review_text: text,
                is_public: isPublic
            })
        });
    }

    async deleteReview(reviewId) {
        return this.request(`reviews.php?action=delete&review_id=${reviewId}`, {
            method: 'DELETE'
        });
    }

    async getReviews(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`reviews.php?action=list&${queryString}`);
    }

    async getUserReviews(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`reviews.php?action=user-reviews&${queryString}`);
    }

    async getBookReviews(bookId, params = {}) {
        const searchParams = new URLSearchParams({ book_id: bookId, ...params }).toString();
        return this.request(`reviews.php?action=book-reviews&${searchParams}`);
    }

    async markReviewHelpful(reviewId) {
        return this.request('reviews.php?action=mark-helpful', {
            method: 'POST',
            body: JSON.stringify({ review_id: reviewId })
        });
    }

    async getRecentReviews(limit = 3) {
        return this.request(`reviews.php?action=recent&limit=${limit}`);
    }

    async getReviewStats() {
        return this.request('reviews.php?action=stats');
    }

    // Favorites APIs
    async addFavorite(bookId) {
        return this.request('favorites.php?action=add', {
            method: 'POST',
            body: JSON.stringify({ book_id: bookId })
        });
    }

    async removeFavorite(bookId) {
        return this.request(`favorites.php?action=remove&book_id=${bookId}`, {
            method: 'DELETE'
        });
    }

    async toggleFavorite(bookId) {
        return this.request('favorites.php?action=toggle', {
            method: 'POST',
            body: JSON.stringify({ book_id: bookId })
        });
    }

    async getFavorites(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`favorites.php?action=list&${queryString}`);
    }

    async checkFavorite(bookId) {
        return this.request(`favorites.php?action=check&book_id=${bookId}`);
    }

    async getFavoritesStats() {
        return this.request('favorites.php?action=stats');
    }

    async exportFavorites(format = 'json') {
        window.open(`${this.baseURL}/api/favorites.php?action=export&format=${format}`, '_blank');
    }

    async getRecommendations(limit = 8) {
        return this.request(`favorites.php?action=recommendations&limit=${limit}`);
    }
}

// Create global API instance
window.api = new BookVibeAPI();

// Utility functions
function showToast(message, type = 'info') {
    // Create toast element if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
    toast.show();

    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function showLoading(element, text = 'Loading...') {
    element.innerHTML = `
        <div class="d-flex align-items-center justify-content-center py-3">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${text}
        </div>
    `;
}

function handleApiError(error, fallbackMessage = 'An error occurred') {
    console.error('API Error:', error);
    const message = error.message || fallbackMessage;
    showToast(message, 'error');
}

// Common authentication check
async function checkAuthStatus() {
    try {
        const response = await api.checkAuth();
        return response.success && response.authenticated;
    } catch (error) {
        return false;
    }
}

// Redirect to login if not authenticated
function requireAuth(redirectUrl = null) {
    checkAuthStatus().then(isAuth => {
        if (!isAuth) {
            const redirect = redirectUrl || window.location.pathname;
            window.location.href = `login.php?redirect=${encodeURIComponent(redirect)}`;
        }
    });
}

// Star rating utility
function createStarRating(rating, interactive = false, callback = null) {
    const starsHTML = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating - fullStars >= 0.5;

    for (let i = 1; i <= 5; i++) {
        let starClass = 'far fa-star';
        if (i <= fullStars) {
            starClass = 'fas fa-star';
        } else if (i === fullStars + 1 && hasHalfStar) {
            starClass = 'fas fa-star-half-alt';
        }

        const clickHandler = interactive && callback ? `onclick="${callback}(${i})"` : '';
        const interactiveClass = interactive ? 'interactive-star' : '';
        
        starsHTML.push(`<i class="${starClass} star ${interactiveClass}" data-rating="${i}" ${clickHandler}></i>`);
    }

    return starsHTML.join('');
}

// Format numbers
function formatNumber(num) {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}