<?php
session_start();
include 'config.php';  // Include your database connection

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Handle "Delete Product" action
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    
    // Prepare and execute SQL query to delete product
    $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt_delete === false) {
        die("Error preparing the statement: " . $conn->error);
    }
    $stmt_delete->bind_param("i", $product_id);
    
    if ($stmt_delete->execute()) {
        echo "<p class='success'>Product removed successfully.</p>";
    } else {
        echo "<p class='error'>Error removing product: " . $stmt_delete->error . "</p>";
    }
    $stmt_delete->close();
}

// Prepare the SQL query to fetch products
$sql = "SELECT id, product_name, product_description, price, product_image FROM products";
$result = $conn->query($sql);

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <h2>Admin - Manage Products</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Product ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price (PHP)</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_description']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" width="100"></td>
                    <td>
                        <form action="admin_edit_product.php" method="get" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <button type="submit" class="edit-btn">Edit</button>
                        </form>
                        <form action="admin_products.php" method="post" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <button type="submit" name="delete_product" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>

    <p><a href="admin_add_product.php" class="add-product-btn">Add New Product</a></p>
    <p><a href="admin_login.php">Logout</a></p>
    <a href="admin_dashboard.php">back</a>
    <?php
    // Free result and close connection
    if ($result) {
        $result->free();
    }
    $conn->close();
    ?>
    
</body>
</html>
