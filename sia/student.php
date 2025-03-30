<?php
include("connection.php");

header("Content-Type: application/json"); // Ensure JSON response format
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $query = "SELECT student_id, first_name, last_name, email, gender, course, address, birthdate, profile_image, 
                TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) AS age 
                FROM user_tb";

    $statement = $connection->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []);
} catch (PDOException $th) {
    error_log("Database error: " . $th->getMessage());
    echo json_encode(['error' => "Database error occurred."]);
}
?>
