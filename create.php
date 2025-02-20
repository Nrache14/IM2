<?php
include('connection.php');

$query = "INSERT INTO prod_tb (prod_name) values ('" . $_GET['pname'] . "')";
$statement = $connection->prepare($query);
$res = $statement->execute();

if ($res) {
    echo json_encode(["res" => "success"]);
} else {
    echo json_encode(["res" => "error", "msg" => "Failes to insert"]);
}
