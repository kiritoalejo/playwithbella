<?php
session_start();
$uploadDir = '../uploads/';

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo "You need to be logged in to upload a profile picture.";
    exit;
}

// Check if the user_id is set in the session
if (!isset($_SESSION['id'])) {
    echo "User ID is not available. Please ensure you are logged in properly.";
    exit;
}

// Check if the upload directory exists, if not create it
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle file upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $tmpName = $_FILES['profile_picture']['tmp_name'];
    $fileName = basename($_FILES['profile_picture']['name']);
    $filePath = $uploadDir . $fileName;

    // Move uploaded file
    if (move_uploaded_file($tmpName, $filePath)) {
        // Update the profile picture in the session
        $_SESSION['profile_picture'] = $fileName;

        // Database update
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=testdb", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "UPDATE users SET profile_picture = :filePath WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['filePath' => $fileName, 'id' => $_SESSION['id']]);

            echo "Profile picture updated successfully.";
        } catch (PDOException $e) {
            echo "Error updating profile picture: " . $e->getMessage();
        }
    } else {
        echo "Failed to move the uploaded file. Please check directory permissions.";
    }
} else {
    echo "No file uploaded or there was an upload error.";
}
?>
