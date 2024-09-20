<?php
session_start();
include 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $home_address = $_POST['home_address'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $user_id = $_SESSION['user_id'];

    // Fetch current password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $hashed_password)) {
        // Update user information
        if (!empty($new_password)) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone_number = ?, home_address = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $username, $email, $phone_number, $home_address, $hashed_new_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone_number = ?, home_address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $email, $phone_number, $home_address, $user_id);
        }
        $stmt->execute();
        $stmt->close();
        echo "<p class='success'>Settings updated successfully.</p>";
    } else {
        echo "<p class='error'>Current password is incorrect.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <h2>Settings</h2>
        <form action="settings.php" method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
            <br>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <br>

            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
            <br>

            <label for="home_address">Home Address:</label>
            <input type="text" name="home_address" value="<?php echo htmlspecialchars($user['home_address']); ?>" required>
            <br>

            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" required>
            <br>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password">
            <br>

            <button type="submit">Update Settings</button>
        </form>
    </div>
</body>
</html>
