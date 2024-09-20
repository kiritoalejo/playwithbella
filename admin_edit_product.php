<?php
session_start();
include 'config.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Example categories
$categories = [
    'Sanrio Toys',
    'Sanrio Lamps',
    'Mideer Puzzles',
    'Sanrio Jelly Bags',
    'Mideer Backpack',
    'Sanrio School supplies',
    'Mideer coloring materials',
    'Mideer toys',
    'Mideer school supplies',
    'Mideer lunchbox/bag',
    'Sanrio lunchbox/bag',
    'Other'
];

// Fetch product details
$product_sql = "SELECT * FROM products LIMIT 1";  // Adjust the query to get the desired product
$stmt = $conn->prepare($product_sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch product variations
$variation_sql = "SELECT * FROM product_variations WHERE product_id = ?";
$variation_stmt = $conn->prepare($variation_sql);
$variation_stmt->bind_param("i", $product['id']);
$variation_stmt->execute();
$variations = $variation_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$variation_stmt->close();

// If no product is found
if (!$product) {
    die("No product found in the database.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $price = $_POST['price'];
    $category = $_POST['category']; // Get selected category
    $product_image = $_FILES['product_image']['name'];
    $target = "uploads/" . basename($product_image);

    // Prepare the update statement for the product
    // Prepare the update statement for the product
$update_sql = "UPDATE products SET product_name = ?, product_description = ?, price = ?, category = ?";
if ($product_image) {
    $update_sql .= ", product_image = ?";
}
$update_sql .= " WHERE id = ?";


    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        die("Error preparing update statement: " . $conn->error);
    }

    // Bind parameters and handle file upload
   // Update statement
if ($product_image) {
    $stmt->bind_param("ssssi", $product_name, $product_description, $price, $category, $product_image, $product['id']);
} else {
    $stmt->bind_param("ssssi", $product_name, $product_description, $price, $category, $product['id']);
}


    if ($stmt->execute()) {
        echo "<p class='success'>Product updated successfully.</p>";
    } else {
        echo "<p class='error'>Error updating product: " . $stmt->error . "</p>";
    }

    // Update or add variations
    // Check if any variations are marked for deletion
    if (!empty($_POST['delete_variation'])) {
        foreach ($_POST['delete_variation'] as $delete_id) {
            $delete_sql = "DELETE FROM product_variations WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $delete_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }

    if (!empty($_POST['variation_name'])) {
        foreach ($_POST['variation_name'] as $index => $variation_name) {
            $variation_price = $_POST['variation_price'][$index];
            $variation_id = $_POST['variation_id'][$index];
            $variation_image = $_FILES['variation_image']['name'][$index];
            $target = "uploads/" . basename($variation_image);

            // Check if it's an existing variation or a new one
            if (!empty($variation_id)) {
                // Update existing variation
                $variation_update_sql = "UPDATE product_variations SET variation_name = ?, variation_price = ?";
                if ($variation_image) {
                    $variation_update_sql .= ", variation_image = ?";
                }
                $variation_update_sql .= " WHERE id = ?";
                $variation_update_stmt = $conn->prepare($variation_update_sql);
                
                if ($variation_image) {
                    $variation_update_stmt->bind_param("sdsi", $variation_name, $variation_price, $variation_image, $variation_id);
                    move_uploaded_file($_FILES['variation_image']['tmp_name'][$index], $target);
                } else {
                    $variation_update_stmt->bind_param("sdi", $variation_name, $variation_price, $variation_id);
                }
                $variation_update_stmt->execute();
                $variation_update_stmt->close();
            } else {
                // Insert new variation
                $variation_insert_sql = "INSERT INTO product_variations (product_id, variation_name, variation_price, variation_image) VALUES (?, ?, ?, ?)";
                $variation_insert_stmt = $conn->prepare($variation_insert_sql);
                $variation_insert_stmt->bind_param("isd", $product['id'], $variation_name, $variation_price, $variation_image);
                move_uploaded_file($_FILES['variation_image']['tmp_name'][$index], $target);
                $variation_insert_stmt->execute();
                $variation_insert_stmt->close();
            }
        }
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="admin_styles.css">
    <script>
        <script>
    function addVariation() {
        const variationContainer = document.getElementById('variation-container');
        const variationCount = document.getElementsByClassName('variation').length;
        
        const variationHTML = `
            <div class="variation">
                <label for="variation_name_${variationCount}">Variation Name:</label>
                <input type="text" id="variation_name_${variationCount}" name="variation_name[]" required>

                <label for="variation_price_${variationCount}">Variation Price (PHP):</label>
                <input type="number" id="variation_price_${variationCount}" name="variation_price[]" step="0.01" required>

                <label for="variation_image_${variationCount}">Variation Image:</label>
                <input type="file" id="variation_image_input_${variationCount}" name="variation_image[]" accept="image/*" style="display: none;" required>
                <button type="button" onclick="document.getElementById('variation_image_input_${variationCount}').click();">Upload Image</button>

                <button type="button" onclick="removeVariation(this)">Remove Variation</button>
                <hr>
            </div>
        `;
        variationContainer.insertAdjacentHTML('beforeend', variationHTML);
    }

    function removeVariation(button) {
        button.parentElement.remove();
    }
</script>

</head>
<body>
    <div class="admin-wrapper">
        <h2>Edit Product</h2>
        <form action="admin_edit_product.php" method="post">
            <label for="product_name">Product Name:</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            <br>

            <label for="product_description">Product Description:</label>
            <textarea name="product_description" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
            <br>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            <br>

            <label for="category">Category:</label>
            <select name="category" required>
                <option value="" disabled>Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($product['category'] === $category) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>

            <label for="product_image">Product Image:</label>
            <input type="file" name="product_image">
            <br>
            <img src="uploads/<?php echo htmlspecialchars($product['product_image']); ?>" width="100">
            <br>
        
            <h3>Product Variations</h3>
            <div id="variation-container">
                <?php foreach ($variations as $index => $variation): ?>
                    <div class="variation">
                        <label for="variation_name_<?php echo $index; ?>">Variation Name:</label>
                        <input type="text" id="variation_name_<?php echo $index; ?>" name="variation_name[]" value="<?php echo htmlspecialchars($variation['variation_name']); ?>" required>
                        
                        <label for="variation_price_<?php echo $index; ?>">Variation Price (PHP):</label>
                        <input type="number" step="0.01" id="variation_price_<?php echo $index; ?>" name="variation_price[]" value="<?php echo htmlspecialchars($variation['variation_price']); ?>" required>
                        
                        <label for="variation_image_<?php echo $index; ?>">Variation Image:</label>
                        <input type="file" name="variation_image[]">
                        <?php if ($variation['variation_image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($variation['variation_image']); ?>" width="100">
                        <?php endif; ?>
                        
                        <input type="hidden" name="variation_id[]" value="<?php echo $variation['id']; ?>">
                        <input type="checkbox" name="delete_variation[]" value="<?php echo $variation['id']; ?>"> Delete
                        <button type="button" onclick="removeVariation(this)">Remove</button>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" onclick="addVariation()">Add Variation</button>
            <br>

            <button type="submit">Update Product</button>
        </form>
        <p><a href="admin_products.php">Back to Product List</a></p>
    </div>
</body>
</html>
