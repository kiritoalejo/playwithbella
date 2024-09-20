<?php
session_start();
include 'config.php';  // Include your database connection

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Fetch product details for editing
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT product_name, product_description, price, product_image, category FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($product_name, $product_description, $price, $product_image, $category);
        $stmt->fetch();
    } else {
        echo "<p class='error'>Product not found.</p>";
        exit;
    }
    $stmt->close();
} else {
    echo "<p class='error'>No product ID provided.</p>";
    exit;
}

// Handle editing a product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $image = $_FILES['product_image']['name'];
    
    // Update product image if new image is uploaded
    if ($image) {
        $target_file = "uploads/" . basename($image);
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE products SET product_name = ?, product_description = ?, price = ?, product_image = ?, category = ? WHERE id = ?");
            $stmt->bind_param("ssdssi", $product_name, $product_description, $price, $target_file, $category, $product_id);
        } else {
            echo "<p class='error'>Error uploading image.</p>";
            exit;
        }
    } else {
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, product_description = ?, price = ?, category = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $product_name, $product_description, $price, $category, $product_id);
    }
    
    if ($stmt->execute()) {
        echo "<p class='success'>Product updated successfully.</p>";
    } else {
        echo "<p class='error'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* (Your existing styles) */
    </style>
</head>
<body>
    <h2>Edit Product</h2>
    <form action="edit_product.php?id=<?php echo htmlspecialchars($product_id); ?>" method="post" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>" required><br>
        
        <label for="product_description">Product Description:</label>
        <textarea name="product_description" required><?php echo htmlspecialchars($product_description); ?></textarea><br>
        
        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($price); ?>" required><br>
        
        <label for="category">Category:</label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($category); ?>" required><br>
        
        <label for="product_image">Product Image:</label>
        <input type="file" name="product_image"><br>
        
        <button type="submit" name="edit_product">Update Product</button>
    </form>
    <p><a href="admin_manage_products.php">Back to Manage Products</a></p>
</body>
</html>

<?php
$conn->close();
?>
