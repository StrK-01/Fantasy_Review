<?php 
// fantasy_review/index.php
session_start(); 
include('database/db.php'); 

$query = "SELECT id, title, rating, poster, review_text FROM reviews ORDER BY rating DESC LIMIT 10"; // Select all fields needed
$result = mysqli_query($conn, $query);

$currentPage = basename($_SERVER['PHP_SELF']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fantasy Review Home</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="modal" id="appModal">
    <div class="modal-content-box">
        <div id="fullReviewContent" style="display: none;">
            <h3 id="reviewTitle" style="text-align: left; margin-bottom: 5px;"></h3>
            <p id="reviewRating" style="text-align: left; font-size: 24px; margin-top: 0;"></p>
            <p id="reviewText" style="text-align: left; font-family: Arial, sans-serif; font-size: 16px;"></p>
        </div>
        <p id="modalContent"></p>
        <div class="modal-actions">
            <button class="btn confirm" id="modalConfirm">OK</button>
            <button class="btn cancel" id="modalCancel">Cancel</button>
        </div>
    </div>
</div>

<div class="header">
    <div class="menu-icon" id="menu-toggle">☰</div>
    <h1 class="title">FANTASY REVIEW</h1>
    
    <div class="user-indicator">
        <?php if (isset($_SESSION['username'])): ?>
            <span class="user-welcome">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="pages/account.php?logout=true" class="btn btn-logout">
                Logout
            </a>
        <?php else: ?>
            <a href="pages/account.php" class="btn btn-login">
                Login
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'active-link' : ''; ?>">Home</a>
    <a href="pages/add.php" class="<?php echo $currentPage == 'add.php' ? 'active-link' : ''; ?>">Add Review</a>
    <a href="pages/view.php" class="<?php echo $currentPage == 'view.php' ? 'active-link' : ''; ?>">View All Reviews</a>
    <a href="pages/account.php" class="<?php echo $currentPage == 'account.php' ? 'active-link' : ''; ?>">Account</a>
</div>

<div class="buttons">
    <a href="pages/add.php" class="btn add">+ Add Review</a>
    <a href="pages/view.php" class="btn view">👁 View All</a>
</div>

<div class="main-divider"></div>

<h2 class="list-header">Top Rated</h2>

<div class="ranking-list">
<?php
$rank = 1;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="ranking-item" data-id="<?php echo $row['id']; ?>" onclick="fetchReviewDetails(<?php echo $row['id']; ?>)">
            <div class="poster-box">
                <?php if ($row['poster']): ?>
                    <img src="images/<?php echo $row['poster']; ?>" alt="<?php echo $row['title']; ?>">
                <? else: ?>
                    <img src="images/default_poster.png" alt="No Poster Available">
                <?php endif; ?>
            </div>

            <div class="info">
                <p class="movie-name"><?php echo $rank++ . ". " . htmlspecialchars($row['title']); ?></p>
                <p class="stars">
                    <?php for ($i=1; $i <= $row['rating']; $i++) echo "★"; ?>
                </p>
            </div>
        </div>
    <?php endwhile;
} else {
    echo "<p class='error-message'>Error loading top reviews.</p>";
}
?>
</div>

<script src="js/script.js"></script>

</body>
</html>