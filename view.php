<?php 
// fantasy_review/pages/view.php
session_start(); 
include('../database/db.php'); 

$currentPage = basename($_SERVER['PHP_SELF']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Reviews</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="modal" id="appModal">
    <div class="modal-content-box">
        <p id="modalContent"></p>
        <div class="modal-actions">
            <button class="btn confirm" id="modalConfirm">OK</button>
            <button class="btn cancel" id="modalCancel">Cancel</button>
        </div>
    </div>
</div>

<div class="header">
    <div class="menu-icon" id="menu-toggle">☰</div>
    <h1 class="title">ALL FANTASY REVIEWS</h1>
    
    <div class="user-indicator">
        <?php if (isset($_SESSION['username'])): ?>
            <span class="user-welcome">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="account.php?logout=true" class="btn btn-logout">
                Logout
            </a>
        <?php else: ?>
            <a href="account.php" class="btn btn-login">
                Login
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <a href="../index.php" class="<?php echo $currentPage == 'index.php' ? 'active-link' : ''; ?>">Home</a>
    <a href="add.php" class="<?php echo $currentPage == 'add.php' ? 'active-link' : ''; ?>">Add Review</a>
    <a href="view.php" class="<?php echo $currentPage == 'view.php' ? 'active-link' : ''; ?>">View All Reviews</a>
    <a href="account.php" class="<?php echo $currentPage == 'account.php' ? 'active-link' : ''; ?>">Account</a>
</div>

<a href="../index.php" class="btn back-home">← Back Home</a>
<div class="main-divider"></div>

<h2 class="list-header">All Submitted Reviews</h2>

<form id="searchForm" class="search-form">
    <input type="text" id="searchTitle" placeholder="Search by title..." />
    <button type="submit">Search</button>
</form>

<div class="all-reviews" id="reviewContainer">
    </div>

<script src="../js/script.js"></script>
<script>
    // This script ensures the dynamic loading starts and the search functionality is active.

    // Call the function defined in script.js to populate the reviews when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initial load of all reviews when the page opens
        loadAllReviews(); 

        // Handle Search Form Submission (Preventing default server-side submit)
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchTitle');
        
        if (searchForm && searchInput) {
            searchForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const searchTerm = searchInput.value;
                loadAllReviews(searchTerm);
            });

            // Optional: Trigger search on input change after a short delay (debouncing)
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadAllReviews(searchInput.value);
                }, 500); // Wait 500ms after typing stops
            });
        }
    });
</script>

</body>
</html>