<?php
session_start();
include 'admin_config.php';  // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL query
    $sql = "SELECT id, username, password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("s", $username);

    // Execute the query
    $stmt->execute();

    // Fetch the result
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("location: admin_dashboard.php");
        } else {
            echo "<p class='error'>Invalid username or password.</p>";
        }
    } else {
        echo "<p class='error'>Invalid username or password.</p>";
    }

    // Close statement
    $stmt->close();
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Admin Login</h2>
    <form action="admin_login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
