<?php
include("connection.php");

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['res' => 'error', 'message' => 'Invalid ID provided.']);
        exit;
    }

    // $id = $_POST['id'];
    $id = intval($_POST['id']); 


    try {
        $query = "DELETE FROM user_tb WHERE student_id = :id";
        $statement = $connection->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            echo json_encode(['res' => 'success']);
        } else {
            echo json_encode(['res' => 'error', 'message' => 'User not found or already deleted.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['res' => 'error', 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['res' => 'error', 'message' => 'Invalid request method.']);
}
?>
