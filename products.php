<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Products
{
    function getAllProduct()
    {
        include "connection.php";
        $sql = "SELECT * FROM tbl_products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ? json_encode($result) : 0;
    }

    function updateProduct($json)
    {
        // {"product_id":1001, "productName":"test","price":1000}
        include "connection.php";
        $data = json_decode($json, true);
        $sql = "UPDATE tbl_products SET prod_name = :name, prod_price = :price WHERE prod_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":name", $data["productName"]);
        $stmt->bindParam(":price", $data["price"]);
        $stmt->bindParam(":id", $data["product_id"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function addProduct($json)
    {
        // {"productName":"test","price":1000,"barcode":1011}
        include "connection.php";
        $data = json_decode($json, true);

        if (recordExists($data["barcode"], "tbl_products", "prod_id")) {
            // mo return siya og -1 if ang barcode already exist
            return -1;
        } else if (recordExists($data["productName"], "tbl_products", "prod_name")) {
            // mo return siya og -2 if ang name already exist
            return -2;
        }

        $sql = "INSERT INTO tbl_products (prod_id, prod_name, prod_price) VALUES (:id, :name, :price)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":name", $data["productName"]);
        $stmt->bindParam(":price", $data["price"]);
        $stmt->bindParam(":id", $data["barcode"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }
}

function recordExists($value, $table, $column)
{
    include "connection.php";
    $sql = "SELECT COUNT(*) FROM $table WHERE $column = :value";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":value", $value);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
}

$json = isset($_POST["json"]) ? $_POST["json"] : "0";
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";


$product = new Products();

switch ($operation) {
    case "getAllProduct":
        echo $product->getAllProduct();
        break;
    case "updateProduct":
        echo $product->updateProduct($json);
        break;
    case "addProduct":
        echo $product->addProduct($json);
        break;
    default:
        echo "Invalid operation";
        break;
}
