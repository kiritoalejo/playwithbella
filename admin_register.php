<?php
session_start();
include 'admin_config.php';  // Include your database connection

// Check if the user is already logged in
if (isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true) {
    header("location: admin_dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<p class='error'>Passwords do not match.</p>";
    } else {
        // Prepare SQL query
        $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Bind parameters
        $stmt->bind_param("ss", $username, $hashed_password);

        // Execute the query
        if ($stmt->execute()) {
            echo "<p class='success'>Admin registered successfully.</p>";
        } else {
            echo "<p class='error'>Error registering admin: " . $stmt->error . "</p>";
        }

        // Close statement
        $stmt->close();
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Admin Registration</h2>
    <form action="admin_register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <button type="submit">Register</button>
    </form>
</body>
</html>
