// fantasy_review/js/script.js

// ===================================
// Custom UI Helpers for Messages and Confirmation
// ===================================

/**
 * Shows a custom, non-blocking message modal.
 * @param {string} message The text to display.
 * @param {boolean} isError If true, displays an error style.
 */
function showAppMessage(message, isError = false) {
    const modal = document.getElementById('appModal');
    const modalContent = document.getElementById('modalContent');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalCancel = document.getElementById('modalCancel');
    const fullReviewContent = document.getElementById('fullReviewContent'); // New

    if (modal && modalContent) {
        // Hide the review content section if it's open
        if (fullReviewContent) fullReviewContent.style.display = 'none';

        modalContent.textContent = message;
        modalContent.style.display = 'block'; // Ensure message content is visible

        modal.classList.remove('modal-error', 'modal-confirm', 'modal-review');
        modal.classList.add('modal-message');

        if (isError) {
            modal.classList.add('modal-error');
        }

        modalConfirm.style.display = 'block';
        modalCancel.style.display = 'none';

        modal.style.display = 'flex';

        // ROBUST MODAL FIX: Use onclick to ensure the OK button reliably closes the modal
        modalConfirm.onclick = () => {
            modal.style.display = 'none';
        };
    }
}

/**
 * Shows a custom confirmation modal and returns a Promise.
 * @param {string} message The confirmation question.
 * @returns {Promise<boolean>} True if confirmed, false otherwise.
 */
function showAppConfirm(message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('appModal');
        const modalContent = document.getElementById('modalContent');
        const modalConfirm = document.getElementById('modalConfirm');
        const modalCancel = document.getElementById('modalCancel');
        const fullReviewContent = document.getElementById('fullReviewContent'); // New

        if (modal && modalContent) {
            // Hide the review content section
            if (fullReviewContent) fullReviewContent.style.display = 'none';
            
            modalContent.textContent = message;
            modalContent.style.display = 'block'; // Ensure message content is visible

            modal.classList.remove('modal-error', 'modal-message', 'modal-review');
            modal.classList.add('modal-confirm');

            modalConfirm.style.display = 'block';
            modalCancel.style.display = 'block'; // Show Cancel button for confirmation

            modalConfirm.textContent = 'Confirm';
            modalCancel.textContent = 'Cancel';

            modal.style.display = 'flex';

            modalConfirm.onclick = () => {
                modal.style.display = 'none';
                resolve(true);
            };

            modalCancel.onclick = () => {
                modal.style.display = 'none';
                resolve(false);
            };
        } else {
            // Fallback for missing elements
            resolve(confirm(message));
        }
    });
}

// ===================================
// New: Function to fetch and display single review details
// ===================================

/**
 * Fetches and displays a single review in a modal popup.
 * @param {number} reviewId The ID of the review to fetch.
 */
async function fetchReviewDetails(reviewId) {
    if (!reviewId) return;

    try {
        const response = await fetch(`api.php?id=${reviewId}&action=get_single_review`);
        const result = await response.json();

        if (response.ok && result.status === 'success' && result.review) {
            const review = result.review;
            const modal = document.getElementById('appModal');
            const modalContent = document.getElementById('modalContent');
            const fullReviewContent = document.getElementById('fullReviewContent');
            const modalConfirm = document.getElementById('modalConfirm');
            const modalCancel = document.getElementById('modalCancel');

            // Populate the review details section
            document.getElementById('reviewTitle').textContent = review.title;
            
            let stars = '';
            for (let i = 1; i <= review.rating; i++) {
                stars += '★';
            }
            document.getElementById('reviewRating').textContent = stars;
            document.getElementById('reviewText').textContent = review.review_text;
            
            // Show the review content and hide the generic message content
            if (modalContent) modalContent.style.display = 'none';
            if (fullReviewContent) fullReviewContent.style.display = 'block';

            // Configure modal for viewing (only an OK button)
            modal.classList.remove('modal-error', 'modal-confirm', 'modal-message');
            modal.classList.add('modal-review');
            
            modalConfirm.textContent = 'Close';
            modalConfirm.style.display = 'block';
            modalCancel.style.display = 'none';

            modal.style.display = 'flex';

            // Set the close handler
            modalConfirm.onclick = () => {
                modal.style.display = 'none';
            };

        } else {
            showAppMessage('Error loading review details: ' + (result.message || 'Review not found.'), true);
        }

    } catch (error) {
        console.error('Fetch Review Details Error:', error);
        showAppMessage('A network error occurred while fetching the review.', true);
    }
}


// ===================================
// Authentication Handlers
// ===================================

// Handles user registration
async function handleRegister(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    if (data.password !== data.confirm_password) {
        showAppMessage('Passwords do not match.', true);
        return;
    }

    // Add action for API routing
    data.action = 'register';
    delete data.confirm_password; // Don't send confirm password to API

    try {
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showAppMessage(result.message);
            form.reset();
        } else {
            showAppMessage('Registration failed: ' + result.message, true);
        }
    } catch (error) {
        console.error('Registration Error:', error);
        showAppMessage('A network error occurred during registration.', true);
    }
}

// Handles user login
async function handleLogin(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Add action for API routing
    data.action = 'login';

    try {
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            // Success: Display message and reload to update header indicator
            showAppMessage('Welcome, ' + result.username + '!');
            setTimeout(() => {
                window.location.reload(); 
            }, 1000); // Reload after 1 second
        } else {
            showAppMessage('Login failed: ' + result.message, true);
        }
    } catch (error) {
        console.error('Login Error:', error);
        showAppMessage('A network error occurred during login.', true);
    }
}


// ===================================
// Review Management Handlers
// ===================================

// Handles adding a new review
async function handleAddReview(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Convert rating to integer
    data.rating = parseInt(data.rating);

    try {
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showAppMessage(result.message);
            form.reset();
        } else {
            showAppMessage('Error adding review: ' + result.message, true);
        }
    } catch (error) {
        console.error('Add Review Error:', error);
        showAppMessage('A network error occurred during review submission.', true);
    }
}

// Handles updating an existing review
async function handleEditReview(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Convert id and rating to integers
    data.id = parseInt(data.id);
    data.rating = parseInt(data.rating);

    try {
        const response = await fetch('../api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showAppMessage(result.message);
            // Redirect back to view page after a short delay
            setTimeout(() => {
                window.location.href = 'view.php';
            }, 1000);
        } else {
            showAppMessage('Error updating review: ' + result.message, true);
        }
    } catch (error) {
        console.error('Edit Review Error:', error);
        showAppMessage('A network error occurred during review update.', true);
    }
}

// Builds the HTML for a single review card
function createReviewCard(review) {
    let stars = '';
    for (let i = 1; i <= review.rating; i++) {
        stars += '★';
    }

    const card = document.createElement('div');
    card.className = 'review-card';
    card.setAttribute('data-id', review.id);

    // Escape HTML output
    const safeTitle = (review.title || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const safeReviewText = (review.review_text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    card.innerHTML = `
        <div class="poster-box">
            ${review.poster ? `<img src="../images/${review.poster}" alt="${safeTitle}">` : `<img src="../images/default_poster.png" alt="No Poster Available">`}
        </div>
        <div class="info">
            <h3 class="card-title">${safeTitle}</h3>
            <p class="stars">${stars}</p>
            <p class="card-review-text">${safeReviewText.substring(0, 200) + (safeReviewText.length > 200 ? '...' : '')}</p>
            <div class="actions">
                <a href="edit.php?id=${review.id}" class="btn edit">Edit</a>
                <button class="btn delete" onclick="handleDeleteReview(${review.id}, this)">Delete</button>
            </div>
        </div>
    `;
    return card;
}

// Fetches and displays all reviews
async function loadAllReviews(searchTerm = '') {
    const container = document.getElementById('reviewContainer');
    if (!container) return;

    container.innerHTML = '<p class="loading-message">Loading reviews...</p>';

    try {
        const response = await fetch(`../api.php?search=${encodeURIComponent(searchTerm)}`);
        const result = await response.json();

        if (response.ok && result.status === 'success') {
            container.innerHTML = ''; // Clear loading message

            if (result.reviews && result.reviews.length > 0) {
                result.reviews.forEach(review => {
                    container.appendChild(createReviewCard(review));
                });
            } else {
                container.innerHTML = '<p class="no-reviews">No reviews found matching your criteria.</p>';
            }
        } else {
            container.innerHTML = `<p class="error-message">Error: ${result.message || 'Failed to fetch reviews.'}</p>`;
        }
    } catch (error) {
        console.error('Load All Reviews Error:', error);
        container.innerHTML = '<p class="error-message">A network error occurred.</p>';
    }
}

// Handles review deletion
async function handleDeleteReview(reviewId, buttonElement) {
    const reviewCard = buttonElement.closest('.review-card');
    const reviewTitle = reviewCard ? reviewCard.querySelector('.card-title').textContent : 'this item';

    const confirmed = await showAppConfirm(`Are you sure you want to delete review ID ${reviewId}: "${reviewTitle}"? This cannot be undone.`);

    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch('../api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: reviewId })
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            showAppMessage(result.message);
            // Remove the card from the DOM with animation
            const deletedCard = document.querySelector(`.review-card[data-id="${reviewId}"]`);
            if (deletedCard) {
                deletedCard.style.opacity = '0';
                deletedCard.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    deletedCard.remove();
                    // Re-load to update the list if needed
                    loadAllReviews(document.getElementById('searchTitle')?.value || ''); 
                }, 300);
            }
        } else {
            showAppMessage('Error deleting review: ' + result.message, true);
        }
    } catch (error) {
        console.error('Delete Error:', error);
        showAppMessage('A network error occurred during the delete operation.', true);
    }
}


// ===================================
// DOM Content Loaded Handler (For Account Page)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    // Universal Sidebar Toggle
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // Account Page logic (Tab switching and form handlers)
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');
    const addReviewForm = document.getElementById('addReviewForm');
    
    // Register Form
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    // Login Form
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // Add Review Form
    if (addReviewForm) {
        addReviewForm.addEventListener('submit', handleAddReview);
    }

    // Tab control logic (Account page)
    const tabButtons = document.querySelectorAll('.tab-controls button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove 'active' from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add 'active' to the clicked button
            this.classList.add('active');

            // Hide all form boxes
            document.querySelectorAll('.form-box').forEach(box => {
                box.classList.add('hidden');
            });

            // Show the target form box
            const targetId = this.getAttribute('data-target');
            const targetBox = document.getElementById(targetId);
            if (targetBox) {
                targetBox.classList.remove('hidden');
            }
        });
    });

    // Password visibility toggle logic
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = '🙈'; // Change icon to closed eye
            } else {
                passwordInput.type = 'password';
                this.textContent = '👁️'; // Change icon back to open eye
            }
        });
    });

    // NOTE: loadAllReviews for view.php is handled in the view.php <script> block
});