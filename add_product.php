<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a Product</title>
    <link rel="stylesheet" href="admin_styles.css">
    <script>
        function addToCart(productName) {
            alert(productName + " has been added to your cart!");
        }

        function removeItem(productId) {
            if (confirm("Are you sure you want to remove this item?")) {
                window.location.href = "add_product.php?remove=" + productId;
            }
        }
    </script>
</head>
<body class="add-product-page">
    <h2>Add a Product</h2>
    
    <!-- Product Form -->
    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" required><br>

        <label for="product_description">Description:</label>
        <textarea name="product_description" required></textarea><br>

        <label for="product_quantity">Quantity:</label>
        <input type="number" name="product_quantity" min="1" required><br>

        <label for="product_price">Price:</label>
        <input type="text" name="product_price" required><br>

        <label for="product_image">Product Image:</label>
        <input type="file" name="product_image" required><br><br>

        <input type="submit" name="submit" value="Add Product">
    </form>

    <!-- Display Added Products -->
    <div class="product-list">
        <h3>Available Products</h3>
        <?php
        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'user_database');

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Handle form submission
        if (isset($_POST['submit'])) {
            $productName = $_POST['product_name'];
            $productDescription = $_POST['product_description'];
            $productQuantity = $_POST['product_quantity'];
            $productPrice = $_POST['product_price'];
            $productImage = $_FILES['product_image']['name'];
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($productImage);

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
                $sql = "INSERT INTO products (product_name, product_description, product_quantity, product_image, price)
                        VALUES ('$productName', '$productDescription', '$productQuantity', '$targetFile', '$productPrice')";
                if ($conn->query($sql) === TRUE) {
                    echo "Product added successfully!";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }

        // Handle item removal
        if (isset($_GET['remove'])) {
            $productId = intval($_GET['remove']);
            $sql = "DELETE FROM products WHERE id = $productId";
            if ($conn->query($sql) === TRUE) {
                echo "Product removed successfully!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        // Fetch and display products
        $result = $conn->query("SELECT * FROM products");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='product-item'>";
                echo "<img src='" . $row['product_image'] . "' alt='" . $row['product_name'] . "'>";
                echo "<h4>" . $row['product_name'] . "</h4>";
                echo "<p>" . $row['product_description'] . "</p>";
                echo "<p>Quantity: " . $row['product_quantity'] . "</p>";
                echo "<p>Price: $" . $row['price'] . "</p>";
                echo "<button class='add-to-cart' onclick=\"addToCart('" . $row['product_name'] . "')\">Add to Cart</button>";
                echo "<button class='remove-item' onclick=\"removeItem(" . $row['id'] . ")\">Remove Item</button>";
                echo "</div>";
            }
        } else {
            echo "<p>No products available.</p>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
