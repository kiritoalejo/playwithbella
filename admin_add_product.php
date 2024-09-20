    <?php
    session_start();
    include 'config.php';  // Include your database connection

    // Check if admin is logged in
    if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
        header("location: admin_login.php");
        exit;
    }

    // Predefined categories for the dropdown (you can retrieve these from the database if needed)
    $categories = ['Sanrio Toys', 'Sanrio Lamps', 'Mideer Puzzles', 'Sanrio Jelly Bags', 'Mideer Backpack', 'Sanrio School supplies', 'Mideer coloring materials', 'Mideer toys', 'Mideer school supplies', 'Mideer lunchbox/bag', 'Sanrio lunchbox/bag', 'Other'];  // Example categories

    // Handle "Add Product" form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
        $product_name = $_POST['product_name'];
        $product_description = $_POST['product_description'];
        $price = $_POST['price'];
        $product_image = $_POST['product_image'];  // Assuming this is a URL or file path
        $category = $_POST['category'];  // Get the selected category

        // Prepare and execute SQL query to insert new product
        $stmt = $conn->prepare("INSERT INTO products (product_name, product_description, price, product_image, category) VALUES (?, ?, ?, ?, ?)");

        // Check if the statement preparation failed
        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("ssiss", $product_name, $product_description, $price, $product_image, $category);

        if ($stmt->execute()) {
            // Get the ID of the newly added product
            $product_id = $stmt->insert_id;

            // Handle variations (if any)
            if (!empty($_POST['variation_name'])) {
                foreach ($_POST['variation_name'] as $index => $variation_name) {
                    $variation_price = $_POST['variation_price'][$index];
                    $variation_image = $_POST['variation_image'][$index];  // Assuming this is a URL or file path

                    $variation_stmt = $conn->prepare("INSERT INTO product_variations (product_id, variation_name, variation_price, variation_image) VALUES (?, ?, ?, ?)");

                    // Check if the statement preparation for variation failed
                    if ($variation_stmt === false) {
                        die("Error preparing variation statement: " . $conn->error);
                    }

                    $variation_stmt->bind_param("isds", $product_id, $variation_name, $variation_price, $variation_image);
                    $variation_stmt->execute();
                    $variation_stmt->close();
                }
            }

            echo "<p class='success'>Product and its variations added successfully.</p>";
        } else {
            echo "<p class='error'>Error adding product: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin - Add New Product</title>
        <link rel="stylesheet" href="admin_styles.css">
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

                        <label for="variation_image_${variationCount}">Variation Image URL:</label>
                        <input type="text" id="variation_image_${variationCount}" name="variation_image[]" required>

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
        <h2>Admin - Add New Product</h2>

        <form action="admin_add_product.php" method="post">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>

            <label for="product_description">Product Description:</label>
            <textarea id="product_description" name="product_description" rows="4" required></textarea>

            <label for="price">Price (PHP):</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="product_image">Product Image URL:</label>
            <input type="text" id="product_image" name="product_image" required>

            <!-- Category Dropdown -->
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?= $category ?>"><?= $category ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Variations Section -->
            <h3>Product Variations</h3>
            <div id="variation-container">
                <div class="variation">
                    <label for="variation_name_0">Variation Name:</label>
                    <input type="text" id="variation_name_0" name="variation_name[]" required>

                    <label for="variation_price_0">Variation Price (PHP):</label>
                    <input type="number" id="variation_price_0" name="variation_price[]" step="0.01" required>

                    <label for="variation_image_0">Variation Image URL:</label>
                    <input type="text" id="variation_image_0" name="variation_image[]" required>

                    <button type="button" onclick="removeVariation(this)">Remove Variation</button>
                    <hr>
                </div>
            </div>
            <button type="button" onclick="addVariation()">Add Variation</button>

            <button type="submit" name="add_product">Add Product</button>
        </form>
        <p><a href="admin_products.php">Back to Product List</a></p>
        <p><a href="admin_login.php">Logout</a></p>

    </body>
    </html>
