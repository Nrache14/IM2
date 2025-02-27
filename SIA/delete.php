<?php

include("connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    
    try {
        $query = "DELETE FROM user_tb WHERE id = :id";
        $statement = $connection->prepare($query);
        $statement->execute([':id' => $id]);
        
        echo json_encode(['res' => 'success']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['res' => 'error', 'message' => 'Database error occurred.']);
    }
}


// $query = "DELETE FROM prod_tb WHERE prod_Id = '" . $_GET['id'] . "'";
// $statement = $connection->prepare($query);
// $res = $statement->execute();

// if ($res) {
//     echo json_encode(["res" => "success"]);
// } else {
//     echo json_encode(["res" => "error", "msg" => "Failes to insert"]);
// }
?>