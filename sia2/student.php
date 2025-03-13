<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handling the creation of a new student
    $firstName = trim($_POST["first_name"]);
    $lastName = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $gender = trim($_POST["gender"]);
    $course = trim($_POST["course"]);
    $uaddress = trim($_POST["address"]);
    $birthdate = trim($_POST["birthdate"]);
    $profileImagePath = null;

    // Image upload handling
    if (!empty($_FILES["profileImage"]["name"])) {
        $uploadDir = "profiles/"; // Folder where images will be stored
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create folder if it doesn't exist
        }

        $imageName = time() . "_" . basename($_FILES["profileImage"]["name"]);
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $uploadFile)) {
            $profileImagePath = $uploadFile; // Save this path in the database
        } else {
            echo json_encode(["status" => "error", "message" => "Image upload failed."]);
            exit;
        }
    }

    try {
        $query = "INSERT INTO user_tb (first_name, last_name, email, gender, course, address, birthdate, profile_image) 
                VALUES (:first_name, :last_name, :email, :gender, :course, :uaddress, :birthdate, :profile_image)";
        $stmt = $conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':uaddress', $uaddress, PDO::PARAM_STR);
        $stmt->bindParam(':birthdate', $birthdate, PDO::PARAM_STR);
        $stmt->bindParam(':profile_image', $profileImagePath, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Get the last inserted ID
            $insertedId = $conn->lastInsertId();
            echo json_encode([
                "status" => "success", 
                "message" => "User added successfully!", 
                "image" => $profileImagePath,
                "insertedId" => $insertedId // send back the new user's inserted ID
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add user."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// Fetching the list of users when accessed via GET
try {
    $query = "SELECT *,
            TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
            FROM user_tb"; // Ensure this table name matches your database

    $statement = $conn->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []);
} catch (PDOException $th) {
    error_log("Database error: " . $th->getMessage());
    echo json_encode(['error' => "Database error occurred."]);
}