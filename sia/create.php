<?php
include("connection.php");

header("Content-Type: application/json"); // Ensure JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

$response = ["res" => "error", "message" => "Unknown error occurred."]; // Default response

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate required fields
        $required_fields = ['fname', 'lname', 'email', 'gender', 'course', 'address', 'birthdate'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception(ucfirst($field) . " is required.");
            }
        }

        // Sanitize inputs
        $fname = htmlspecialchars($_POST['fname']);
        $lname = htmlspecialchars($_POST['lname']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $gender = htmlspecialchars($_POST['gender']);
        $course = htmlspecialchars($_POST['course']);
        $address = htmlspecialchars($_POST['address']);
        $birthdate = $_POST['birthdate'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Handle Image Upload
        $profile_image = null;
        // Define the absolute path to the uploads directory
        // Define absolute path to the uploads directory
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        // Ensure the directory exists
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                echo json_encode(["res" => "error", "message" => "Failed to create upload directory."]);
                exit;
            }
        }



        // Check if a profile image was uploaded
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $img_name = basename($_FILES['profile_image']['name']);
            $img_tmp = $_FILES['profile_image']['tmp_name'];
            $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($img_ext, $allowed_exts)) {
                throw new Exception("Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF.");
            }

            // Generate unique filename
            $new_img_name = uniqid('IMG_', true) . '.' . $img_ext;
            $file_path = $uploadDir . $new_img_name;

            if (!move_uploaded_file($img_tmp, $file_path)) {
                throw new Exception("Failed to upload image.");
            }

            $profile_image = $new_img_name; // Store only the filename in the database
        }

        // Insert Data into Database
        $query = "INSERT INTO user_tb (first_name, last_name, email, gender, course, address, birthdate, profile_image) 
                VALUES (:fname, :lname, :email, :gender, :course, :address, :birthdate, :profile_image)";
        $statement = $connection->prepare($query);
        $statement->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':email' => $email,
            ':gender' => $gender,
            ':course' => $course,
            ':address' => $address,
            ':birthdate' => $birthdate,
            ':profile_image' => $profile_image
        ]);

        $response = ["res" => "success", "message" => "User added successfully."];
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $response["message"] = $e->getMessage();
}

// Output JSON response
echo json_encode($response);
exit;
?>
