<?php
session_start();
include 'config.php';  // Include your database connection

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Fetch users from the database
$sql = "SELECT id, username, email, phone_number, home_address FROM users";
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
    <h2>Admin - Users</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Home Address</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['home_address']); ?></td>
                    <td>
                        <!-- Actions such as edit or delete can go here -->
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>

    <?php
    $result->free();
    $conn->close();
    ?>

    <p><a href="admin_login.php">Logout</a></p>
    <p><a href="admin_register.php" class="add-user-btn">Add User</a></p>
    
</body>
</html>
