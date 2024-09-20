<?php
// Database connection settings
$host = 'localhost';
$db_name = 'user_database';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
