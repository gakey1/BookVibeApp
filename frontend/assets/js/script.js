/**
 * BookVibe - Main JavaScript
 * Basic functionality for enhanced user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all functionality
    initializeTooltips();
    initializeStarRatings();
    initializeFormValidation();
    initializeFavorites();
    initializePasswordStrength();
    initializeMobileMenu();
    initializeCharacterCounter();
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    
    if (searchInput && searchButton) {
        // Search on button click
        searchButton.addEventListener('click', function() {
            performSearch();
        });
        
        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
    
    function performSearch() {
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            // For now, redirect to browse page with search parameter
            window.location.href = `browse.php?search=${encodeURIComponent(query)}`;
        } else {
            showNotification('Please enter at least 2 characters to search', 'warning');
        }
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                
                // Re-enable after 5 seconds as failsafe
                setTimeout(() => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });
    
    // Card hover effects
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Fade in animation for page content
    const content = document.querySelector('main, .container');
    if (content) {
        content.classList.add('fade-in');
    }
    
});

/**
 * Show notification to user
 * @param {string} message - The message to display
 * @param {string} type - Type of notification (success, error, warning, info)
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${getIconForType(type)} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

/**
 * Get appropriate icon for notification type
 * @param {string} type - Notification type
 * @returns {string} Font Awesome icon class
 */
function getIconForType(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

/**
 * Format number with appropriate suffix (K, M)
 * @param {number} num - Number to format
 * @returns {string} Formatted number string
 */
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

/**
 * Create star rating display with proper half-star support
 * @param {number} rating - Rating value (0-5)
 * @param {boolean} showNumber - Whether to show numeric rating
 * @returns {string} HTML string for star rating
 */
function createStarRating(rating, showNumber = true) {
    const fullStars = Math.floor(rating);
    const decimal = rating - fullStars;
    const hasHalfStar = decimal >= 0.3 && decimal < 0.8;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    let html = '<span class="star-rating">';
    
    // Full stars
    for (let i = 0; i < fullStars; i++) {
        html += '<i class="fas fa-star star filled"></i>';
    }
    
    // Half star (only show if rating is between .3 and .8)
    if (hasHalfStar) {
        html += '<i class="fas fa-star star half-filled"></i>';
    }
    
    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
        html += '<i class="far fa-star star empty"></i>';
    }
    
    if (showNumber) {
        html += ` <span class="rating-number ms-1">${rating.toFixed(1)}</span>`;
    }
    
    html += '</span>';
    return html;
}

/**
 * Update existing star rating elements on the page
 * @param {string} selector - CSS selector for rating containers
 */
function updateStarRatings(selector = '[data-rating]') {
    document.querySelectorAll(selector).forEach(element => {
        const rating = parseFloat(element.dataset.rating);
        if (rating && rating > 0) {
            element.innerHTML = createStarRating(rating, false);
        }
    });
}

/**
 * Debounce function to limit function calls
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
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

// Star Rating System
function initializeStarRatings() {
    // Initialize interactive star rating inputs
    const ratingContainers = document.querySelectorAll('.star-rating-input');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star-btn');
        const input = container.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const rating = index + 1;
                input.value = rating;
                updateStarDisplay(stars, rating);
            });
            
            star.addEventListener('mouseenter', () => {
                updateStarDisplay(stars, index + 1);
            });
        });
        
        container.addEventListener('mouseleave', () => {
            const currentRating = parseInt(input.value) || 0;
            updateStarDisplay(stars, currentRating);
        });
    });
    
    // Initialize display-only star ratings
    updateStarRatings();
}

function updateStarDisplay(stars, rating) {
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('filled');
            star.classList.remove('empty');
        } else {
            star.classList.remove('filled');
            star.classList.add('empty');
        }
    });
}

// Enhanced Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                validateInput(input);
            });
        });
    });
}

function validateInput(input) {
    const isValid = input.checkValidity();
    const feedback = input.parentNode.querySelector('.invalid-feedback');
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        
        if (feedback) {
            if (input.validity.valueMissing) {
                feedback.textContent = 'This field is required.';
            } else if (input.validity.typeMismatch) {
                feedback.textContent = 'Please enter a valid ' + input.type + '.';
            } else if (input.validity.tooShort) {
                feedback.textContent = `Minimum ${input.minLength} characters required.`;
            }
        }
    }
}

// Favorites System
function initializeFavorites() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    const heartIcons = document.querySelectorAll('.heart-icon');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.dataset.bookId;
            const isFavorited = this.classList.contains('favorited');
            
            if (isFavorited) {
                removeFavorite(bookId, this);
            } else {
                addFavorite(bookId, this);
            }
        });
    });
    
    // Handle heart icon clicks on book cards
    heartIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const bookId = this.dataset.bookId;
            const isFavorited = this.classList.contains('favorited');
            
            toggleHeartIcon(this, !isFavorited, bookId);
        });
    });
}

function toggleHeartIcon(icon, addFavorite, bookId) {
    if (addFavorite) {
        icon.classList.add('favorited');
        icon.innerHTML = '<i class="fas fa-heart text-danger"></i>';
        
        // AJAX call to add favorite
        $.ajax({
            url: '../backend/api/favorites.php',
            method: 'POST',
            data: JSON.stringify({book_id: bookId, action: 'add'}),
            contentType: 'application/json',
            success: function() {
                showNotification('Added to favorites!', 'success');
            },
            error: function() {
                icon.classList.remove('favorited');
                icon.innerHTML = '<i class="far fa-heart"></i>';
                showNotification('Please login to add favorites', 'warning');
            }
        });
    } else {
        icon.classList.remove('favorited');
        icon.innerHTML = '<i class="far fa-heart"></i>';
        
        // AJAX call to remove favorite
        $.ajax({
            url: '../backend/api/favorites.php',
            method: 'POST',
            data: JSON.stringify({book_id: bookId, action: 'remove'}),
            contentType: 'application/json',
            success: function() {
                showNotification('Removed from favorites', 'info');
            },
            error: function() {
                icon.classList.add('favorited');
                icon.innerHTML = '<i class="fas fa-heart text-danger"></i>';
            }
        });
    }
}

function addFavorite(bookId, button) {
    button.classList.add('favorited');
    button.innerHTML = '<i class="fas fa-heart text-danger"></i> Favorited';
    
    $.ajax({
        url: '../backend/api/favorites.php',
        method: 'POST',
        data: JSON.stringify({book_id: bookId, action: 'add'}),
        contentType: 'application/json',
        success: function() {
            showNotification('Added to favorites!', 'success');
        },
        error: function() {
            button.classList.remove('favorited');
            button.innerHTML = '<i class="far fa-heart"></i> Add to Favorites';
            showNotification('Please login to add favorites', 'warning');
        }
    });
}

function removeFavorite(bookId, button) {
    if (confirm('Remove this book from favorites?')) {
        button.classList.remove('favorited');
        button.innerHTML = '<i class="far fa-heart"></i> Add to Favorites';
        
        $.ajax({
            url: '../backend/api/favorites.php',
            method: 'POST',
            data: JSON.stringify({book_id: bookId, action: 'remove'}),
            contentType: 'application/json',
            success: function() {
                showNotification('Removed from favorites', 'info');
            },
            error: function() {
                button.classList.add('favorited');
                button.innerHTML = '<i class="fas fa-heart text-danger"></i> Favorited';
            }
        });
    }
}

// Password Strength Indicator
function initializePasswordStrength() {
    const passwordInput = document.querySelector('#password');
    const strengthBar = document.querySelector('#passwordStrength');
    const strengthText = document.querySelector('#strengthText');
    
    if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', () => {
            const strength = calculatePasswordStrength(passwordInput.value);
            updatePasswordStrengthDisplay(strength, strengthBar, strengthText);
        });
    }
}

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    if (strength <= 2) return 'weak';
    if (strength <= 4) return 'medium';
    return 'strong';
}

function updatePasswordStrengthDisplay(strength, bar, text) {
    bar.className = 'password-strength strength-' + strength;
    
    const messages = {
        weak: 'Weak password',
        medium: 'Medium strength',
        strong: 'Strong password'
    };
    
    if (text) {
        text.textContent = messages[strength];
        text.className = 'form-text text-' + 
            (strength === 'weak' ? 'danger' : strength === 'medium' ? 'warning' : 'success');
    }
}

// Enhanced Mobile Menu
function initializeMobileMenu() {
    const menuToggle = document.querySelector('.navbar-toggler');
    const mobileMenu = document.querySelector('#navbarNav');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('show');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.remove('show');
            }
        });
    }
}

// Character Counter for Textareas
function initializeCharacterCounter() {
    const textareas = document.querySelectorAll('[data-max-length]');
    
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.dataset.maxLength);
        const counter = document.createElement('div');
        counter.className = 'char-counter text-muted small';
        counter.textContent = `0 / ${maxLength}`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', () => {
            const length = textarea.value.length;
            counter.textContent = `${length} / ${maxLength}`;
            
            if (length > maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
    });
}

// Enhanced Tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const text = e.target.dataset.tooltip;
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = text;
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.position = 'fixed';
            tooltip.style.backgroundColor = '#333';
            tooltip.style.color = 'white';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.zIndex = '9999';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
        });
        
        element.addEventListener('mouseleave', () => {
            document.querySelectorAll('.tooltip-custom').forEach(t => t.remove());
        });
    });
}

// Export utility functions for use in other scripts
window.BookVibe = {
    showNotification,
    addFavorite,
    removeFavorite,
    createStarRating,
    updateStarRatings,
    formatNumber,
    debounce
};
