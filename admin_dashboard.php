<?php
session_start();
include 'admin_config.php';

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="admin-wrapper">
        <h2>Admin Dashboard</h2>
        <a href="admin_orders.php">Manage Orders</a>
        <a href="admin_products.php">Manage Products</a>
        <a href="admin_users.php">Manage Users</a>
        <a href="admin_settings.php">Settings</a>
    </div>
</body>
</html>
