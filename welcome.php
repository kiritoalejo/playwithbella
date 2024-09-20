<?php
session_start();
include 'config.php';  // Include your database connection

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Get the selected category from the query string, default to all products if not set
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Modify the SQL query to filter by category if one is selected
$sql = "SELECT id, product_name, product_description, price, product_image FROM products";
if ($category) {
    $sql .= " WHERE category = ?";
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}

// Bind the category parameter only if a category is selected
if ($category) {
    $stmt->bind_param("s", $category);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Error executing the query: " . $conn->error);
}

// Initialize cart session if not already done
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart action
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $selected_variation = isset($_POST['variation']) ? $_POST['variation'] : '';

    // Fetch the variation price
    $stmt = $conn->prepare("SELECT variation_price FROM product_variations WHERE product_id = ? AND variation_name = ?");
    $stmt->bind_param("is", $product_id, $selected_variation);
    $stmt->execute();
    $variation = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if the variation exists and get the price
    $variation_price = isset($variation['variation_price']) ? $variation['variation_price'] : 0;

    if ($variation_price > 0) {
        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['id'] == $product_id && $cart_item['variation'] == $selected_variation) {
                $cart_item['quantity']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'product_name' => $_POST['product_name'],
                'variation' => $selected_variation,
                'variation_price' => $variation_price,
                'quantity' => 1
            ];
        }

        echo "<p class='success'>Product added to your cart!</p>";
    } else {
        echo "<p class='error'>Selected variation price not found.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar {
            float: left;
            width: 20%;
            background: #f8f9fa;
            padding: 10px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #000;
            background: #fff;
            margin-bottom: 5px;
            border: 1px solid #ddd;
        }
        .sidebar a:hover {
            background: #007bff;
            color: white;
        }
        .content {
            float: right;
            width: 75%;
        }
        .menu-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .menu-item img {
            max-width: 150px;
            display: block;
            cursor: pointer;
        }
        .price {
            font-weight: bold;
        }
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.8); 
        }
        .modal-content {
            margin: 15% auto; 
            padding: 20px;
            width: 80%; 
            max-width: 600px; 
        }
        .modal-content img {
            width: 100%;
            height: auto;
        }
        .close {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
    </style>

    <script>
        function updateVariation(selectElement, productId) {
            var selectedOption = selectElement.options[selectElement.selectedIndex];
            var price = selectedOption.getAttribute('data-price');
            var image = selectedOption.getAttribute('data-image');

            // Update the price and image dynamically
            if (price) {
                document.getElementById('price-' + productId).innerText = 'PHP ' + price;
            }
            if (image) {
                document.getElementById('image-' + productId).src = image;
            }
        }

        // Modal functionality
        function openModal(imageSrc) {
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("modalImage");
    modal.style.display = "block";
    modalImg.src = imageSrc;
}

function closeModal() {
    var modal = document.getElementById("myModal");
    modal.style.display = "none";
}

    </script>
</head>
<body>

    <div class="wrapper">
        <h2>Welcome</h2>
        <p>Hello, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to your dashboard.</p>

        <div class="sidebar">
            <h3>Categories</h3>
            <a href="welcome.php">All Products</a>
            <a href="welcome.php?category=Sanrio Jelly Bags">Sanrio Toys</a>
            <a href="welcome.php?category=Sanrio Lamps">Sanrio Lamps</a>
            <a href="welcome.php?category=Mideer Backpack">Mideer Backpack</a>
            <a href="welcome.php?category=Sanrio Backpack">Sanrio Backpack</a>
            <a href="welcome.php?category=Sanrio School supplies">Sanrio School supplies</a>
            <a href="welcome.php?category=Mideer coloring materials">Mideer coloring materials</a>
            <a href="welcome.php?category=Mideer toys">Mideer toys</a>
            <a href="welcome.php?category=Mideer school supplies">Mideer school supplies</a>
            <a href="welcome.php?category=Mideer lunchbox/bag">Mideer lunchbox/bag</a>
            <a href="welcome.php?category=Sanrio lunchbox/bag">Sanrio lunchbox/bag</a>
        </div>

        <div class="content">
            <h3>Product Menu</h3>
            <div class="menu">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="menu-item">
                        <img id="image-<?php echo $product['id']; ?>" 
     src="<?php echo htmlspecialchars($product["product_image"]); ?>" 
     alt="<?php echo htmlspecialchars($product["product_name"]); ?>" 
     onclick="openModal('<?php echo htmlspecialchars($product["product_image"]); ?>')">

                            <div>
                                <h4><?php echo htmlspecialchars($product["product_name"]); ?></h4>
                                <p><?php echo htmlspecialchars($product["product_description"]); ?></p>
                                <p><strong>Price:</strong> <span class="price" id="price-<?php echo $product['id']; ?>">PHP <?php echo htmlspecialchars($product["price"]); ?></span></p>

                                <?php
                                $product_id = $product['id'];
                                $variation_sql = "SELECT * FROM product_variations WHERE product_id = ?";
                                $stmt_variations = $conn->prepare($variation_sql);
                                if ($stmt_variations === false) {
                                    die("Error preparing the statement: " . $conn->error);
                                }
                                $stmt_variations->bind_param("i", $product_id);
                                $stmt_variations->execute();
                                $variation_result = $stmt_variations->get_result();
                                ?>

                                <form action="welcome.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">

                                    <?php if ($variation_result->num_rows > 0): ?>
                                        <label for="variation">Select Variation:</label>
                                        <select name="variation" required onchange="updateVariation(this, <?php echo $product['id']; ?>)">
                                            <?php while ($variation = $variation_result->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($variation['variation_name']); ?>"
                                                        data-price="<?php echo isset($variation['variation_price']) ? htmlspecialchars($variation['variation_price']) : '0'; ?>"
                                                        data-image="<?php echo htmlspecialchars($variation['variation_image']); ?>">
                                                    <?php echo htmlspecialchars($variation['variation_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    <?php else: ?>
                                        <p>No variations available.</p>
                                    <?php endif; ?>

                                    <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products available in this category.</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="clear: both;"></div>

        <a href="cart.php" class="view-cart">View Cart</a>
    </div>

    <!-- Modal for enlarged image -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImage" src="" alt="Enlarged Image">
        </div>
    </div>

</body>
</html>

<?php
$result->free();
$conn->close();
?>
