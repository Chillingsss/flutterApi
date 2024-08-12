<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

class Sales
{
    function getBeginningBalance()
    {
        include "connection.php";
        $sql = "SELECT * FROM tbl_beginning_balance";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : json_encode(['beginning_balance' => 0]);
    }

    // Other functions...
}

$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

$sales = new Sales();

switch ($operation) {
    case "getBeginningBalance":
        echo $sales->getBeginningBalance();
        break;
    // Handle other operations...
    default:
        echo json_encode(["message" => "Invalid operation.", "status" => -1]);
        break;
}
