<?php
include("connection.php");

try {
    $query = "SELECT *, 
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
