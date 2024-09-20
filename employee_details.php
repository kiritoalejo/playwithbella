<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="employee-details-page">
    <h2>Employee Details</h2>
    
    <?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'user_management');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch and display employee details
    if (isset($_GET['id'])) {
        $employeeId = intval($_GET['id']);
        $result = $conn->query("SELECT * FROM employees WHERE id = $employeeId");

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<p><strong>First Name:</strong> " . $row['first_name'] . "</p>";
            echo "<p><strong>Last Name:</strong> " . $row['last_name'] . "</p>";
            echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
            echo "<p><strong>Phone Number:</strong> " . $row['phone_number'] . "</p>";
            echo "<p><strong>Position:</strong> " . $row['position'] . "</p>";
            echo "<p><strong>Hire Date:</strong> " . $row['hire_date'] . "</p>";
            echo "<p><strong>Birthday:</strong> " . $row['birthday'] . "</p>";
            echo "<p><strong>Profile Picture:</strong><br> <img src='" . $row['profile_picture'] . "' alt='Profile Picture' width='150'></p>";
        } else {
            echo "<p>No employee found with this ID.</p>";
        }
    } else {
        echo "<p>No employee ID provided.</p>";
    }

    $conn->close();
    ?>

    <!-- Add Product Button -->
    <a href="add_product.php" class="add-product-button">Add a Product</a>

</body>
</html>
