<?php
session_start();
include 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Handle Remove from Cart
if (isset($_POST['remove_from_cart'])) {
    $product_id = intval($_POST['product_id']);
    $variation = $_POST['variation'];  // Get the selected variation

    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $product_id && $item['variation'] == $variation) {
            unset($_SESSION['cart'][$index]);  // Remove item from cart
            $_SESSION['cart'] = array_values($_SESSION['cart']);  // Reindex the array
            echo "<p class='success'>Product removed from your cart.</p>";
            break;
        }
    }
}

// Handle Checkout
if (isset($_POST['checkout'])) {
    $user_id = $_SESSION['id'];
    $delivery_option = $_POST['delivery_option'];

    $total = 0;
    $order_items = [];
    foreach ($_SESSION['cart'] as $cart_item) {
        $total += $cart_item['price'] * $cart_item['quantity'];
        $order_items[] = $cart_item;
    }

    // Prepare and execute order insertion
    if ($stmt_order = $conn->prepare("INSERT INTO orders (user_id, total, order_date, status, delivery_option) VALUES (?, ?, NOW(), 'Pending', ?)")) {
        $stmt_order->bind_param("ids", $user_id, $total, $delivery_option);
        if ($stmt_order->execute()) {
            $order_id = $stmt_order->insert_id;
            foreach ($order_items as $item) {
                // Prepare and execute order items insertion
                if ($stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, variation_name) VALUES (?, ?, ?, ?, ?, ?)")) {
                    $stmt_item->bind_param("iissis", $order_id, $item['id'], $item['product_name'], $item['price'], $item['quantity'], $item['variation']);
                    $stmt_item->execute();
                    $stmt_item->close();
                } else {
                    echo "Error preparing order items statement: " . $conn->error;
                }
            }
            $_SESSION['cart'] = [];
            echo "<p class='success'>Order placed successfully!</p>";
        } else {
            echo "<p class='error'>Something went wrong. Please try again later. Error: " . $stmt_order->error . "</p>";
        }
        $stmt_order->close();
    } else {
        echo "Error preparing order statement: " . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <h2>Your Cart</h2>

        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Product Name</th>
                    <th>Variation</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                <?php
                $cart_total = 0;
                foreach ($_SESSION['cart'] as $item):
                    $item_total = $item['variation_price'] * $item['quantity'];
                    $cart_total += $item_total;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['variation']); ?></td>
                        <td>PHP <?php echo htmlspecialchars($item['variation_price']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>PHP <?php echo htmlspecialchars($item_total); ?></td>
                        <td>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <input type="hidden" name="variation" value="<?php echo htmlspecialchars($item['variation']); ?>">
                                <button type="submit" name="remove_from_cart" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5"><strong>Total</strong></td>
                    <td><strong>PHP <?php echo htmlspecialchars($cart_total); ?></strong></td>
                </tr>
            </table>

            <form action="cart.php" method="post">
                <label for="delivery_option">Choose Delivery Option:</label>
                <select name="delivery_option" required>
                    <option value="Lalamove">Lalamove</option>
                    <option value="J&T Express">J&T Express</option>
                </select>
                <button type="submit" name="checkout" class="checkout-btn">Checkout</button>
            </form>
        <?php endif; ?>

        <p><a href="welcome.php">Back to Product Menu</a></p>
    </div>
</body>
</html>
