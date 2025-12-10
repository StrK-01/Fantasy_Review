<?php
// fantasy_review/database/db.php
$host = "127.0.0.1";
$user = "root";
$password = "";
// CRITICAL CHANGE: Database name is now "fantasy_review"
$database = "fantasy_review"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>