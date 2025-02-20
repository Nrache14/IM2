<?php
include('connection.php');

$query = "DELETE FROM prod_tb WHERE prod_Id = '" . $_GET['id'] . "'";
$statement = $connection->prepare($query);
$res = $statement->execute();

if ($res) {
    echo json_encode(["res" => "success"]);
} else {
    echo json_encode(["res" => "error", "msg" => "Failes to insert"]);
}
