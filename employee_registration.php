<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Employee Registration Form</h2>
    <form action="employee_registration.php" method="POST" enctype="multipart/form-data">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" required><br>

        <label for="position">Position:</label>
        <select name="position" required>
            <option value="server">Server</option>
            <option value="barista">Barista</option>
            <option value="guard">Guard</option>
            <option value="janitor">Janitor</option>
        </select><br>

        <label for="hire_date">Hire Date:</label>
        <input type="date" name="hire_date" required><br>

        <label for="birthday">Birthday:</label>
        <input type="date" name="birthday" required><br>

        <label for="profile_picture">Profile Picture:</label>
        <input type="file" name="profile_picture"><br><br>

        <input type="submit" name="submit" value="Register Employee">
    </form>
</body>
</html>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $position = $_POST['position'];
    $hire_date = $_POST['hire_date'];
    $birthday = $_POST['birthday'];

    // Handle profile picture upload
    $profile_picture = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        $profile_picture = $target_dir . basename($_FILES["profile_picture"]["name"]);
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture)) {
            // File uploaded successfully
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $sql = "INSERT INTO employees (first_name, last_name, email, phone_number, position, hire_date, birthday, profile_picture)
            VALUES ('$first_name', '$last_name', '$email', '$phone_number', '$position', '$hire_date', '$birthday', '$profile_picture')";

    if ($conn->query($sql) === TRUE) {
        // Get the last inserted ID
        $employee_id = $conn->insert_id;
        // Redirect to the confirmation page with employee ID
        header("Location: employee_details.php?id=$employee_id");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
