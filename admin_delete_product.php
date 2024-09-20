<?php
session_start();
include 'admin_config.php';

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

$product_id = $_GET['id'];

$delete_sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->close();

header("Location: admin_products.php");
exit;
?>
