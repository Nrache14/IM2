<?php
// save_user.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sia";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle file upload
if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['profile_image']['name']);
    $uploadFilePath = $uploadDir . uniqid() . '_' . $fileName;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFilePath)) {
        // Insert user data into the database
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);

        $sql = "INSERT INTO users (name, email, profile_image) VALUES ('$name', '$email', '$uploadFilePath')";

        if ($conn->query($sql) === true) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File upload failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload error.']);
}

$conn->close();
