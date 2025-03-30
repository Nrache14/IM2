<?php
include('connection.php');

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['id']) || empty($_POST['id']) ||
        !isset($_POST['fname']) || !isset($_POST['lname']) || 
        !isset($_POST['email']) || !isset($_POST['gender']) || 
        !isset($_POST['course']) || !isset($_POST['address']) || 
        !isset($_POST['birthdate'])
    ) {
        echo json_encode(["res" => "error", "msg" => "All fields are required."]);
        exit;
    }

    $id = $_POST['id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $address = $_POST['address'];
    $birthdate = $_POST['birthdate'];

    try {
        // Fetch the existing profile image
        $query = "SELECT profile_image FROM user_tb WHERE student_id = :id";
        $statement = $connection->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        $currentProfileImage = $user['profile_image'] ?? null;

        // Set profile image: If a new one is uploaded, update; otherwise, keep the existing one
        $profile_image = $currentProfileImage;

        if (!empty($_FILES['profile_image']['name'])) {
            $uploadDir = "uploads/";
            $fileName = basename($_FILES['profile_image']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($fileType), $allowedTypes)) {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {
                    $profile_image = $fileName; // Update profile image
                } else {
                    echo json_encode(["res" => "error", "msg" => "Failed to upload profile image."]);
                    exit;
                }
            } else {
                echo json_encode(["res" => "error", "msg" => "Invalid image format. Allowed: jpg, jpeg, png, gif."]);
                exit;
            }
        }

        // Update query (always update the profile image field)
        $query = "UPDATE user_tb SET 
                    first_name = :fname, 
                    last_name = :lname, 
                    email = :email, 
                    gender = :gender, 
                    course = :course, 
                    address = :address, 
                    birthdate = :birthdate, 
                    profile_image = :profile_image
                WHERE student_id = :id";

        $statement = $connection->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->bindParam(':fname', $fname);
        $statement->bindParam(':lname', $lname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':gender', $gender);
        $statement->bindParam(':course', $course);
        $statement->bindParam(':address', $address);
        $statement->bindParam(':birthdate', $birthdate);
        $statement->bindParam(':profile_image', $profile_image);

        $res = $statement->execute();

        if ($res) {
            echo json_encode(["res" => "success"]);
        } else {
            echo json_encode(["res" => "error", "msg" => "Failed to update user."]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["res" => "error", "msg" => "Database error occurred."]);
    }
} else {
    echo json_encode(["res" => "error", "msg" => "Invalid request method."]);
}
?>
