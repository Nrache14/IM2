<?php
include('connection.php');

header("Content-Type: application/json");

try {
    $query = "SELECT COUNT(*) as total FROM user_tb";
    $statement = $connection->prepare($query);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["count" => $result['total']]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["count" => 0]);
}
?>
