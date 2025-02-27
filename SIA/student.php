<?php
include("connection.php");

try {
    $query = "SELECT * FROM user_tb";
    $statement = $connection->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []);
} catch (PDOException $th) {
    error_log("Database error: " . $th->getMessage());
    echo json_encode(['error' => "Database error occurred."]);
}
?>
