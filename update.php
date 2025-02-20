<?php
include('connection.php');

$query = "UPDATE prod_tb SET prod_name ='" . $_GET['pname'] . "' WHERE prod_Id = '" . $_GET['id'] . "'";
$statement = $connection->prepare($query);
$res = $statement->execute();

if ($res) {
    echo json_encode(["res" => "success"]);
} else {
    echo json_encode(["res" => "error", "msg" => "Failes to insert"]);
}
