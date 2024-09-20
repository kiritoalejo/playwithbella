<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    if (!empty($name) && !empty($email) && !empty($message)) {
        // Process the message (e.g., save to database or send an email)
        // For simplicity, we'll just display a success message
        echo "Thank you for your message, $name! We will get back to you soon.";
    } else {
        echo "All fields are required.";
    }
} else {
    header("location: welcome.php");
    exit;
}
?>
