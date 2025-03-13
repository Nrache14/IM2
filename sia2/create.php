<?php
include("connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $address = $_POST['address'];
    $birthdate = $_POST['birthdate'];
    $profileImagePath = null;


    try {
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
            ':profile_image' => $profileImagePath

        ]);
        $lastId = $connection->lastInsertId(); // Get the last inserted ID
        echo json_encode(['res' => 'success', 'insertedId' => $lastId]); // Return the ID
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['res' => 'error', 'message' => 'Database error occurred.']);
    }
}