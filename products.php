<?php
include("connection.php");

try {
    $query = "SELECT * FROM prod_tb";
    $statement = $connection->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []);
} catch (PDOException $th) {
    error_log("Database error: " . $th->getMessage());
    // http_response_code(500);
    echo json_encode(['error' => "Database error occurred."]);
}
?>
