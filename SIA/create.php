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

    try {
        $query = "INSERT INTO user_tb (first_name, last_name, email, gender, course, address, birthdate) 
                VALUES (:fname, :lname, :email, :gender, :course, :address, :birthdate)";
        $statement = $connection->prepare($query);
        $statement->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':email' => $email,
            ':gender' => $gender,
            ':course' => $course,
            ':address' => $address,
            ':birthdate' => $birthdate
        ]);
        echo json_encode(['res' => 'success']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['res' => 'error', 'message' => 'Database error occurred.']);
    }
}

// include('connection.php');

// $query = "INSERT INTO prod_tb (prod_name) values ('" . $_GET['pname'] . "')";
// $statement = $connection->prepare($query);
// $res = $statement->execute();

// if ($res) {
//     echo json_encode(["res" => "success"]);
// } else {
//     echo json_encode(["res" => "error", "msg" => "Failes to insert"]);
// }

