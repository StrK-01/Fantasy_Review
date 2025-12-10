<?php 
// fantasy_review/pages/account.php
session_start(); 

$show_login = true; 

// --- LOGOUT LOGIC ---
if (isset($_GET['logout'])) { 
    session_unset();
    session_destroy();
    header("Location: account.php"); 
    exit;
}
// --------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Account</title>
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
    <h1 class="title">USER ACCOUNT</h1>
    
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
    <a href="add.php">Add Review</a>
    <a href="view.php">View All Reviews</a>
    <a href="account.php" class="active-link">Account</a>
</div>

<a href="../index.php" class="btn back-home">← Back Home</a>
<div class="main-divider"></div>

<h2 class="list-header">User Account</h2>

<div class="tab-controls">
    <button data-target="loginFormBox" class="<?php echo $show_login ? 'active' : ''; ?>">Login</button>
    <button data-target="registerFormBox" class="<?php echo !$show_login ? 'active' : ''; ?>">Create Account</button>
</div>

<div id="loginFormBox" class="form-box <?php echo !$show_login ? 'hidden' : ''; ?>">
    <h3>Login</h3>
    <form id="loginForm">
        <label for="login_username">Username:</label>
        <input type="text" id="login_username" name="username" required>
        
        <label for="login_password">Password:</label>
        <div class="password-container">
            <input type="password" id="login_password" name="password" required>
            <span class="toggle-password" data-target="login_password">👁️</span>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
</div>

<div id="registerFormBox" class="form-box <?php echo $show_login ? 'hidden' : ''; ?>">
    <h3>Create New Account</h3>
    <form id="registerForm">
        <label for="register_username">Username:</label>
        <input type="text" id="register_username" name="username" required>
        
        <label for="register_password">Password:</label>
        <div class="password-container">
            <input type="password" id="register_password" name="password" required>
            <span class="toggle-password" data-target="register_password">👁️</span>
        </div>
        
        <label for="register_confirm_password">Confirm Password:</label>
        <div class="password-container">
            <input type="password" id="register_confirm_password" name="confirm_password" required>
            <span class="toggle-password" data-target="register_confirm_password">👁️</span>
        </div>
        
        <button type="submit" class="btn add">Register</button>
    </form>
</div>


<script src="../js/script.js"></script>

</body>
</html>