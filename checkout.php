<?php
session_start();
include 'config.php';

if (empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $total = array_reduce($_SESSION['cart'], function($sum, $item) {
        return $sum + $item['price'] * $item['quantity'];
    }, 0);

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, delivery_option) VALUES (?, ?, 'Pending', ?)");
    $stmt->bind_param("ids", $user_id, $total, $_POST['delivery_option']);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        foreach ($_SESSION['cart'] as $item) {
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_name, variation_name, product_price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt_item->bind_param("issdi", $order_id, $item['product_name'], $item['variation'], $item['price'], $item['quantity']);
            $stmt_item->execute();
        }

        $_SESSION['cart'] = [];
        echo "Order placed successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
</head>
<body>
    <h2>Checkout</h2>
    <form method="post">
        <label for="delivery_option">Select Delivery Option:</label>
        <select name="delivery_option" required>
            <option value="Lalamove">Lalamove</option>
            <option value="J&T Express">J&T Express</option>
        </select>
        <button type="submit">Place Order</button>
    </form>
</body>
</html>
