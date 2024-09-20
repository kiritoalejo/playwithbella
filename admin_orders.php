<?php
session_start();
include 'config.php';  // Include your database connection

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Handle "Finished Order" action
if (isset($_POST['finish_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Update order status in the database
    $stmt_update = $conn->prepare("UPDATE orders SET status = 'Finished' WHERE id = ?");
    $stmt_update->bind_param("i", $order_id);
    
    if ($stmt_update->execute()) {
        // Fetch the customer ID
        $stmt_user = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
        $stmt_user->bind_param("i", $order_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        
        if ($result_user->num_rows > 0) {
            $user_row = $result_user->fetch_assoc();
            $user_id = $user_row['user_id'];
            
            // Create notification message
            $message = "Dear Customer,\n\nYour order is now being prepared. We will notify you once it is ready.\n\nThank you for your patience!";
            
            // Fetch username for notification
            $stmt_username = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt_username->bind_param("i", $user_id);
            $stmt_username->execute();
            $result_username = $stmt_username->get_result();
            $user_row = $result_username->fetch_assoc();
            $username = $user_row['username'];
            $stmt_username->close();

            // Insert notification into the database
            $stmt_notify = $conn->prepare("INSERT INTO notifications (username, message) VALUES (?, ?)");
            $stmt_notify->bind_param("ss", $username, $message);
            
            if ($stmt_notify->execute()) {
                echo "<p class='success'>Order marked as finished and notification sent to the customer's account.</p>";
            } else {
                echo "<p class='error'>Error inserting notification: " . $stmt_notify->error . "</p>";
            }
            $stmt_notify->close();
        }
        $stmt_user->close();
    } else {
        echo "<p class='error'>Error updating order status: " . $stmt_update->error . "</p>";
    }
    $stmt_update->close();
}

// Handle "Remove Order" action
if (isset($_POST['remove_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Delete the order and its items from the database
    $stmt_delete_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt_delete_items->bind_param("i", $order_id);
    $stmt_delete_items->execute();
    $stmt_delete_items->close();

    $stmt_delete_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt_delete_order->bind_param("i", $order_id);
    
    if ($stmt_delete_order->execute()) {
        echo "<p class='success'>Order removed successfully.</p>";
    } else {
        echo "<p class='error'>Error removing order: " . $stmt_delete_order->error . "</p>";
    }
    $stmt_delete_order->close();
}

// Prepare the SQL query to include delivery_option
$sql = "SELECT orders.id, orders.total, orders.order_date, orders.status, orders.delivery_option, users.username, users.phone_number, users.home_address
        FROM orders 
        JOIN users ON orders.user_id = users.id 
        ORDER BY orders.id DESC";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}

// Execute the query
if (!$stmt->execute()) {
    die("Error executing the statement: " . $stmt->error);
}

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Orders</title>
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .order-items {
            margin-left: 20px;
            margin-top: 10px;
        }
        .finish-btn, .remove-btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .finish-btn:hover, .remove-btn:hover {
            background-color: #0056b3;
        }
        .remove-btn {
            background-color: #dc3545;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
        .add-product-btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .add-product-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Admin - Orders</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total (PHP)</th>
                <th>Date</th>
                <th>Status</th>
                <th>Delivery Option</th>
                <th>Items</th>
                <th>Phone Number</th>
                <th>Home Address</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['total']); ?></td>
                    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['delivery_option']); ?></td>
                    <td>
                        <ul class="order-items">
                        <?php
                        // Fetch order items for this order
                        $order_id = $row['id'];
$stmt_items = $conn->prepare("SELECT product_name, product_price, quantity, variation_name FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);

if (!$stmt_items->execute()) {
    echo "<p class='error'>Error fetching order items: " . $stmt_items->error . "</p>";
} else {
    $result_items = $stmt_items->get_result();
    if ($result_items->num_rows > 0) {
        while ($item = $result_items->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($item['product_name']) . " (" . htmlspecialchars($item['variation_name']) . ") - " . htmlspecialchars($item['quantity']) . "x @ PHP " . htmlspecialchars($item['product_price']) . "</li>";
        }
    } else {
        echo "<li>No items found for this order.</li>";
    }
}
$stmt_items->close();
?>
                        </ul>
                    </td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['home_address']); ?></td>
                    <td>
                        <?php if ($row['status'] !== 'Finished'): ?>
                            <form action="admin_orders.php" method="post" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button type="submit" name="finish_order" class="finish-btn">Mark as Finished</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($row['status'] === 'Finished'): ?>
                            <form action="admin_orders.php" method="post" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button type="submit" name="remove_order" class="remove-btn">Remove Order</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>

    <p><a href="admin_login.php">Logout</a></p>
    <p><a href="admin_dashboard.php" class="add-product-btn">Manage Products</a></p>
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
