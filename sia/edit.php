<?php
include('connection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "SELECT * FROM user_tb WHERE student_id = :id";
    $statement = $connection->prepare($query);
    $statement->bindParam(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit;
    }
} else {
    echo "Invalid request!";
    exit;
}
?>
