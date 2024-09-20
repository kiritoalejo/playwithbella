<?php
session_start();
include 'config.php';  // Include your database connection

// Handle registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $home_address = $_POST['home_address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<p class='error'>Passwords do not match.</p>";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone_number, home_address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $phone_number, $home_address, $password_hash);
        
        if ($stmt->execute()) {
            echo "<p class='success'>Registration successful. You can now <a href='login.php'>login</a>.</p>";
        } else {
            echo "<p class='error'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* (Your existing styles) */
    </style>
</head>
<body>
    <h2>Register</h2>
    <form action="register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        
        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" required><br>
        
        <label for="home_address">Home Address:</label>
        <input type="text" name="home_address" required><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required><br>
        
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" required><br>
        
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>

<?php
$conn->close();
?>
