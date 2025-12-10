<?php 
// fantasy_review/pages/edit.php
session_start(); 
//this file is for the edit function
include('../database/db.php'); 

$currentPage = basename($_SERVER['PHP_SELF']); 

// Check if an ID was provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect back to view page if no valid ID is found
    header('Location: view.php');
    exit;
}

$id = $_GET['id'];
$review = null;

// Fetch the existing review data from the database
$query = "SELECT * FROM reviews WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $review = $result->fetch_assoc();
} else {
    // Review not found, redirect
    header('Location: view.php');
    exit;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Review: <?php echo htmlspecialchars($review['title']); ?></title>
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
    <h1 class="title">EDIT REVIEW</h1>
    
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

<a href="view.php" class="btn back-home">← Back to All Reviews</a>
<div class="main-divider"></div>

<h2 class="list-header">Edit: <?php echo htmlspecialchars($review['title']); ?></h2>

<div class="form-box">
    <form id="editReviewForm">
        <input type="hidden" id="reviewId" name="id" value="<?php echo htmlspecialchars($review['id']); ?>">

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($review['title']); ?>">

        <label for="rating">Rating (1-5):</label>
        <input type="number" id="rating" name="rating" min="1" max="5" required value="<?php echo htmlspecialchars($review['rating']); ?>">
        
        <label for="review_text">Review:</label>
        <textarea id="review_text" name="review_text" rows="8" required><?php echo htmlspecialchars($review['review_text']); ?></textarea>
        
        <button type="submit" class="btn edit">Update Review</button>
    </form>
</div>

<script src="../js/script.js"></script> 
<script>
    // Sidebar toggle (local JS)
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // Attach the handler for the UPDATE operation if the form exists
    const editForm = document.getElementById('editReviewForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditReview); 
    }
</script>
</body>
</html>