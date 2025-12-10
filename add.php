<?php 
// fantasy_review/pages/add.php
session_start(); 
// include('../database/db.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Review</title>
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
    <h1 class="title">ADD NEW REVIEW</h1>
    
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
    <a href="../index.php">Home</a>
    <a href="add.php" class="active-link">Add Review</a>
    <a href="view.php">View All Reviews</a>
    <a href="account.php">Account</a>
</div>

<a href="../index.php" class="btn back-home">← Back Home</a>
<div class="main-divider"></div>

<h2 class="list-header">Submit New Review</h2>

<div class="form-box">
    <form id="addReviewForm">
        <label for="title">Title of Movie/Book/Game:</label>
        <input type="text" id="title" name="title" required>

        <label for="review_text">Review:</label>
        <textarea id="review_text" name="review_text" required></textarea>

        <label for="rating">Rating (1-5):</label>
        <input type="number" id="rating" name="rating" min="1" max="5" required>

        <label for="poster">Poster Filename (e.g., my_poster.jpg):</label>
        <input type="text" id="poster" name="poster">
        
        <button type="submit" class="btn add">Add Review</button>
    </form>
</div>


<script src="../js/script.js"></script>

</body>
</html>