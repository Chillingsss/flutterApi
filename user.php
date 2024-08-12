<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Data
{
    // Function to log in user
    function loginUser($json)
    {
        include "connection.php";
        $json = json_decode($json, true);

        try {
            $sql = "SELECT * FROM tbl_users WHERE user_username = :loginUsername";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':loginUsername', $json['loginUsername']);

            if ($stmt->execute()) {
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($data)) {
                    $storedPassword = $data[0]['user_password'];

                    if ($json['loginPassword'] === $storedPassword) {
                        session_start();

                        $_SESSION["userDetails"] = [
                            "id" => $data[0]["user_id"],
                            "fullname" => $data[0]["user_fullName"],
                            "username" => $data[0]["user_username"],
                            "user_level" => $data[0]["user_level"]
                        ];

                        $_SESSION["isLoggedIn"] = true;

                        if ($data[0]['user_level'] == 'admin') {
                            return json_encode(array("status" => 1, "message" => "Login successful. Redirecting to admin.", "user_level" => "admin", "data" => $data));
                        } else {
                            return json_encode(array("status" => 1, "message" => "Login successful. Redirecting to user.", "user_level" => "user", "data" => $data));
                        }
                    } else {
                        return json_encode(array("status" => -1, "message" => "Incorrect password."));
                    }
                } else {
                    return json_encode(array("status" => -1, "message" => "No data found."));
                }
            } else {
                throw new Exception("Error executing SQL statement.");
            }
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            error_log("PDOException: " . $errorMsg);
            return json_encode(array("status" => -1, "title" => "Database error.", "message" => $errorMsg));
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            error_log("Exception: " . $errorMsg);
            return json_encode(array("status" => -1, "title" => "An error occurred.", "message" => $errorMsg));
        } finally {
            $stmt = null;
            $conn = null;
        }
    }


    function verifyAdminPassword($json)
    {
        include "connection.php";

        $data = json_decode($json, true);
        $password = isset($data['password']) ? $data['password'] : '';

        try {
            $sql = "SELECT user_password FROM tbl_users WHERE user_level = 'admin'";
            $stmt = $conn->prepare($sql);

            // Execute the statement
            if ($stmt->execute()) {
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($data as $user) {
                    // Log both the provided and stored passwords for debugging
                    error_log("Password to check: " . $password);
                    error_log("Stored password: " . $user['user_password']);


                    if ($password === $user['user_password']) {
                        return json_encode(array("status" => 1, "message" => "Password is valid."));
                    }
                }
                return json_encode(array("status" => -1, "message" => "Invalid admin password."));
            } else {
                throw new Exception("Error executing SQL statement.");
            }
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            error_log("PDOException: " . $errorMsg);
            return json_encode(array("status" => -1, "title" => "Database error.", "message" => $errorMsg));
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            error_log("Exception: " . $errorMsg);
            return json_encode(array("status" => -1, "title" => "An error occurred.", "message" => $errorMsg));
        } finally {
            $stmt = null;
            $conn = null;
        }
    }








    function getBeginningBalance()
    {
        include "connection.php";
        $sql = "SELECT * FROM tbl_beginning_balance";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : 0;
    }

    function updateBeginningBalance($json)
    {
        // {"amount":500}
        include "connection.php";
        $data = json_decode($json, true);
        $sql = "UPDATE tbl_beginning_balance SET beginning_balance = :amount";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":amount", $data["amount"]);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? 1 : 0;
    }

    function getAllCashiers()
    {
        include "connection.php";
        $sql = "SELECT * FROM tbl_users WHERE user_level = 'user'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
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


$operation = isset($_POST["operation"]) ? $_POST["operation"] : "Invalid";
$json = isset($_POST["json"]) ? $_POST["json"] : "";


$data = new Data();
switch ($operation) {
    case "loginUser":
        echo $data->loginUser($json);
        break;
    case "verifyAdminPassword":
        echo $data->verifyAdminPassword($json);
        break;
    case "getBeginningBalance":
        echo $data->getBeginningBalance();
        break;
    case "updateBeginningBalance":
        echo $data->updateBeginningBalance($json);
        break;
    case "getAllCashiers":
        echo $data->getAllCashiers();
        break;
    default:
        echo json_encode(array("status" => -1, "message" => "Invalid operation."));
}
?>