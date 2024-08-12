<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Sales
{

    function saveTransaction($json)
    {
        include "connection.php";
        $json = json_decode($json, true);
        $master = $json["master"];
        $detail = $json["detail"];
        $dateTime = getCurrentDateTime();

        try {
            $conn->beginTransaction();
            $sql = "INSERT INTO tbl_sales (sale_userId, sale_cashTendered, sale_change, sale_totalAmount, sale_date) 
                    VALUES(:userId, :cashTendered, :change, :totalAmount, :dateTime)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userId", $master["userId"]);
            $stmt->bindParam(":cashTendered", $master["cashTendered"]);
            $stmt->bindParam(":change", $master["change"]);
            $stmt->bindParam(":totalAmount", $master["totalAmount"]);
            $stmt->bindParam(":dateTime", $dateTime);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $saleId = $conn->lastInsertId();
                $sql = "INSERT INTO tbl_sale_item(sale_item_saleId, sale_item_productId, sale_item_quantity, sale_item_price) 
                        VALUES(:saleId, :productId, :quantity, :price)";
                foreach ($detail as $item) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":saleId", $saleId);
                    $stmt->bindParam(":productId", $item["productId"]);
                    $stmt->bindParam(":quantity", $item["quantity"]);
                    $stmt->bindParam(":price", $item["price"]);
                    $stmt->execute();
                }
                $conn->commit();
                return json_encode(["success" => true]);
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            return json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }


    // function getZReport()
    // {
    //     include "connection.php";
    //     date_default_timezone_set('Asia/Manila'); // Set PHP timezone
    //     $conn->exec("SET time_zone = '+08:00';"); // Set MySQL timezone

    //     try {
    //         $sql = "SELECT a.sale_id, d.user_fullname, a.sale_cashTendered, a.sale_change, a.sale_totalAmount, 
    //                 DATE_FORMAT(a.sale_date, '%Y-%m-%d %r') AS sale_date, 
    //                 b.sale_item_productId, b.sale_item_quantity, b.sale_item_price, c.prod_name AS product_name 
    //                 FROM tbl_sales a 
    //                 INNER JOIN tbl_sale_item b ON a.sale_id = b.sale_item_saleId 
    //                 INNER JOIN tbl_products c ON b.sale_item_productId = c.prod_id 
    //                 INNER JOIN tbl_users d ON a.sale_userId = d.user_id 
    //                 WHERE DATE(a.sale_date) = CURDATE() 
    //                 ORDER BY a.sale_id, b.sale_item_productId";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->execute();

    //         $sales = [];
    //         if ($stmt->rowCount() > 0) {
    //             $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //             foreach ($rs as $row) {
    //                 $saleId = $row['sale_id'];
    //                 if (!isset($sales[$saleId])) {
    //                     $sales[$saleId] = [
    //                         'user_username' => $row['user_fullname'],
    //                         'sale_cashTendered' => $row['sale_cashTendered'],
    //                         'sale_change' => $row['sale_change'],
    //                         'sale_totalAmount' => $row['sale_totalAmount'],
    //                         'sale_date' => $row['sale_date'],
    //                         'items' => []
    //                     ];
    //                 }
    //                 $sales[$saleId]['items'][] = [
    //                     'sale_item_productId' => $row['sale_item_productId'],
    //                     'sale_item_quantity' => $row['sale_item_quantity'],
    //                     'sale_item_price' => $row['sale_item_price'],
    //                     'product_name' => $row['product_name']
    //                 ];
    //             }
    //         }

    //         return json_encode(array_values($sales));
    //     } catch (PDOException $e) {
    //         return json_encode(['error' => $e->getMessage()]);
    //     }
    // }


    function getZReport()
    {
        include "connection.php";
        try {
            $sql = "SELECT a.sale_id, d.user_fullname, a.sale_cashTendered, a.sale_change, a.sale_totalAmount, a.sale_date, 
                    b.sale_item_productId, b.sale_item_quantity, b.sale_item_price, c.prod_name AS product_name FROM tbl_sales a 
                    INNER JOIN tbl_sale_item b ON a.sale_id = b.sale_item_saleId 
                    INNER JOIN tbl_products c ON b.sale_item_productId = c.prod_id 
                    INNER JOIN tbl_users d ON a.sale_userId = d.user_id 
                    WHERE a.sale_date = CURDATE() 
                    ORDER BY a.sale_id, b.sale_item_productId";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $sales = [];
            if ($stmt->rowCount() > 0) {
                $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rs as $row) {
                    $saleId = $row['sale_id'];
                    if (!isset($sales[$saleId])) {
                        $sales[$saleId] = [
                            'user_username' => $row['user_fullname'],
                            'sale_cashTendered' => $row['sale_cashTendered'],
                            'sale_change' => $row['sale_change'],
                            'sale_totalAmount' => $row['sale_totalAmount'],
                            'sale_date' => $row['sale_date'],
                            'items' => []
                        ];
                    }
                    $sales[$saleId]['items'][] = [
                        'sale_item_productId' => $row['sale_item_productId'],
                        'sale_item_quantity' => $row['sale_item_quantity'],
                        'sale_item_price' => $row['sale_item_price'],
                        'product_name' => $row['product_name']
                    ];
                }
            }

            return json_encode(array_values($sales));
        } catch (PDOException $e) {
            return 0;
        }
    }


    function getZReportWithSelectedDate($json)
    {
        // {"from":"2024-08-02","to":"2024-08-03"}
        include "connection.php";
        $json = json_decode($json, true);

        $fromDate = $json['from'];
        $toDate = $json['to'];



        try {
            $sql = "SELECT a.sale_id, d.user_fullname, a.sale_cashTendered, a.sale_change, a.sale_totalAmount, a.sale_date, 
              b.sale_item_productId, b.sale_item_quantity, b.sale_item_price, c.prod_name AS product_name 
              FROM tbl_sales a 
              INNER JOIN tbl_sale_item b ON a.sale_id = b.sale_item_saleId 
              INNER JOIN tbl_products c ON b.sale_item_productId = c.prod_id 
              INNER JOIN tbl_users d ON a.sale_userId = d.user_id 
              WHERE DATE(a.sale_date) >= :from AND DATE(a.sale_date) <= :to
              ORDER BY a.sale_date DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":from", $fromDate);
            $stmt->bindParam(":to", $toDate);
            $stmt->execute();

            $sales = [];
            if ($stmt->rowCount() > 0) {
                $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rs as $row) {
                    $saleId = $row['sale_id'];
                    if (!isset($sales[$saleId])) {
                        $sales[$saleId] = [
                            'user_username' => $row['user_fullname'],
                            'sale_cashTendered' => $row['sale_cashTendered'],
                            'sale_change' => $row['sale_change'],
                            'sale_totalAmount' => $row['sale_totalAmount'],
                            'sale_date' => $row['sale_date'],
                            'items' => []
                        ];
                    }
                    $sales[$saleId]['items'][] = [
                        'sale_item_productId' => $row['sale_item_productId'],
                        'sale_item_quantity' => $row['sale_item_quantity'],
                        'sale_item_price' => $row['sale_item_price'],
                        'product_name' => $row['product_name']
                    ];
                }
            }

            return json_encode(array_values($sales));
        } catch (PDOException $e) {
            return 0;
        }
    }



    // function getZAllReport()
    // {
    //     include "connection.php";
    //     $sql = "SELECT a.sale_id, d.user_fullname, a.sale_cashTendered, a.sale_change, a.sale_totalAmount, 
    //         DATE_FORMAT(a.sale_date, '%Y-%m-%d %r') AS sale_date, 
    //         b.sale_item_productId, b.sale_item_quantity, b.sale_item_price, c.prod_name AS product_name 
    //         FROM tbl_sales a 
    //         INNER JOIN tbl_sale_item b ON a.sale_id = b.sale_item_saleId 
    //         INNER JOIN tbl_products c ON b.sale_item_productId = c.prod_id 
    //         INNER JOIN tbl_users d ON a.sale_userId = d.user_id 
    //         ORDER BY a.sale_id DESC";
    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute();
    //     $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     $groupedSales = [];
    //     foreach ($sales as $row) {
    //         $saleId = $row['sale_id'];
    //         if (!isset($groupedSales[$saleId])) {
    //             $groupedSales[$saleId] = [
    //                 'sale_id' => $row['sale_id'],
    //                 'user_username' => $row['user_fullname'],
    //                 'sale_cashTendered' => $row['sale_cashTendered'],
    //                 'sale_change' => $row['sale_change'],
    //                 'sale_totalAmount' => $row['sale_totalAmount'],
    //                 'sale_date' => $row['sale_date'],
    //                 'items' => []
    //             ];
    //         }
    //         $groupedSales[$saleId]['items'][] = [
    //             'sale_item_productId' => $row['sale_item_productId'],
    //             'sale_item_quantity' => $row['sale_item_quantity'],
    //             'sale_item_price' => $row['sale_item_price'],
    //             'product_name' => $row['product_name']
    //         ];
    //     }

    //     return json_encode(array_values($groupedSales));
    // }


    function getShiftReport($json)
    {
        include "connection.php";
        $json = json_decode($json, true);

        try {

            $sql = "
            SELECT a.sale_id, d.user_fullname, a.sale_cashTendered, a.sale_change, a.sale_totalAmount, 
                   DATE_FORMAT(a.sale_date, '%Y-%m-%d %r') AS sale_date, 
                   b.sale_item_productId, b.sale_item_quantity, b.sale_item_price, c.prod_name AS product_name,
                   (SELECT SUM(a2.sale_totalAmount) 
                    FROM tbl_sales a2 
                    WHERE a2.sale_userId = :userId AND DATE(a2.sale_date) = CURDATE()
                   ) AS total_for_today
                FROM tbl_sales a 
                INNER JOIN tbl_sale_item b ON a.sale_id = b.sale_item_saleId 
                INNER JOIN tbl_products c ON b.sale_item_productId = c.prod_id 
                INNER JOIN tbl_users d ON a.sale_userId = d.user_id 
                WHERE d.user_id = :userId AND DATE(a.sale_date) = CURDATE()
                ORDER BY a.sale_id, b.sale_item_productId";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userId", $json["userId"]);
            $stmt->execute();

            $sales = [];
            $totalForToday = 0;

            if ($stmt->rowCount() > 0) {
                $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rs as $row) {
                    $saleId = $row['sale_id'];
                    if (!isset($sales[$saleId])) {
                        $sales[$saleId] = [
                            'user_username' => $row['user_fullname'],
                            'sale_cashTendered' => $row['sale_cashTendered'],
                            'sale_change' => $row['sale_change'],
                            'sale_totalAmount' => $row['sale_totalAmount'],
                            'sale_date' => $row['sale_date'],
                            'items' => []
                        ];
                    }
                    $sales[$saleId]['items'][] = [
                        'sale_item_productId' => $row['sale_item_productId'],
                        'sale_item_quantity' => $row['sale_item_quantity'],
                        'sale_item_price' => $row['sale_item_price'],
                        'product_name' => $row['product_name']
                    ];

                    // Update total amount for today
                    $totalForToday = $row['total_for_today'];
                }
            }

            return json_encode([
                'sales' => array_values($sales),
                'total_for_today' => $totalForToday
            ]);
        } catch (PDOException $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    function getShiftAdminReport($json)
    {
        // {"userId":1, "from":"2024-08-02","to":"2024-08-03"}
        include "connection.php";
        $json = json_decode($json, true);
        try {
            $sql = "SELECT a.sale_id, d.user_fullname, a.sale_cashTendered, a.sale_change, a.sale_totalAmount, a.sale_date, 
      b.sale_item_productId, b.sale_item_quantity, b.sale_item_price, c.prod_name AS product_name FROM tbl_sales a 
      INNER JOIN tbl_sale_item b ON a.sale_id = b.sale_item_saleId 
      INNER JOIN tbl_products c ON b.sale_item_productId = c.prod_id 
      INNER JOIN tbl_users d ON a.sale_userId = d.user_id 
      WHERE a.sale_userId = :userId AND DATE(a.sale_date) >= :from AND DATE(a.sale_date) <= :to
      ORDER BY a.sale_id, b.sale_item_productId";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":userId", $json["userId"]);
            $stmt->bindParam(":from", $json["from"]);
            $stmt->bindParam(":to", $json["to"]);

            $stmt->execute();

            $sales = [];
            if ($stmt->rowCount() > 0) {
                $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rs as $row) {
                    $saleId = $row['sale_id'];
                    if (!isset($sales[$saleId])) {
                        $sales[$saleId] = [
                            'sale_id' => $row['sale_id'],
                            'user_username' => $row['user_fullname'],
                            'sale_cashTendered' => $row['sale_cashTendered'],
                            'sale_change' => $row['sale_change'],
                            'sale_totalAmount' => $row['sale_totalAmount'],
                            'sale_date' => $row['sale_date'],
                            'items' => []
                        ];
                    }
                    $sales[$saleId]['items'][] = [
                        'sale_item_productId' => $row['sale_item_productId'],
                        'sale_item_quantity' => $row['sale_item_quantity'],
                        'sale_item_price' => $row['sale_item_price'],
                        'product_name' => $row['product_name']
                    ];
                }
            }

            return json_encode(array_values($sales));
        } catch (PDOException $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }


    function getTotalAmountForCurrentMonth()
    {
        include "connection.php";
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        $sql = "SELECT DATE(sale_date) AS date, SUM(sale_totalAmount) AS totalAmount 
            FROM tbl_sales 
            WHERE sale_date >= :firstDayOfMonth AND sale_date <= :lastDayOfMonth
            GROUP BY DATE(sale_date)
            ORDER BY DATE(sale_date)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":firstDayOfMonth", $firstDayOfMonth);
        $stmt->bindParam(":lastDayOfMonth", $lastDayOfMonth);
        $stmt->execute();
        return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    function getBoughtProductsForThisMonth()
    {
        include "connection.php";
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        // Adjusted SQL query to select product names and sold quantities
        $sql = "SELECT a.prod_name, SUM(b.sale_item_quantity) AS Sold  
            FROM tbl_products a 
            INNER JOIN tbl_sale_item b ON a.prod_id = b.sale_item_productId 
            INNER JOIN tbl_sales c ON b.sale_item_saleId = c.sale_id
            WHERE c.sale_date >= :firstDayOfMonth AND c.sale_date <= :lastDayOfMonth
            GROUP BY a.prod_name";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":firstDayOfMonth", $firstDayOfMonth);
        $stmt->bindParam(":lastDayOfMonth", $lastDayOfMonth);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];

        foreach ($results as $index => $row) {
            $data[] = [
                'name' => $row['prod_name'],
                'sold' => $row['Sold']
            ];
        }

        return json_encode($data);
    }



    function getThisMonthSales()
    {
        include "connection.php";
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        $sql = "SELECT SUM(sale_totalAmount) AS totalAmount FROM tbl_sales WHERE sale_date >= :firstDayOfMonth AND sale_date <= :lastDayOfMonth";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":firstDayOfMonth", $firstDayOfMonth);
        $stmt->bindParam(":lastDayOfMonth", $lastDayOfMonth);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
    }

    function getLastMonthSales()
    {
        include "connection.php";
        $firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month'));
        $lastDayOfLastMonth = date('Y-m-t', strtotime('-1 month'));
        $sql = "SELECT SUM(sale_totalAmount) AS totalAmount FROM tbl_sales WHERE sale_date >= :firstDayOfLastMonth AND sale_date <= :lastDayOfLastMonth";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":firstDayOfLastMonth", $firstDayOfLastMonth);
        $stmt->bindParam(":lastDayOfLastMonth", $lastDayOfLastMonth);
        $stmt->execute();
        return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
    }


} //user

function getCurrentDate()
{
    $today = new DateTime("now", new DateTimeZone('Asia/Manila'));
    return $today->format('Y-m-d');
}

date_default_timezone_set('Asia/Manila');

function getCurrentDateTime()
{
    return date('Y-m-d H:i:s');
}



$json = isset($_POST["json"]) ? $_POST["json"] : "{}";
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "";

$sales = new Sales();

$response = [];

$sales = new Sales();

switch ($operation) {
    case "saveTransaction":
        echo $sales->saveTransaction($json);
        break;
    case "getZReport":
        echo $sales->getZReport();
        break;
    case "getShiftReport":
        echo $sales->getShiftReport($json);
        break;
    case "getZReportWithSelectedDate":
        echo $sales->getZReportWithSelectedDate($json);
        break;
    case "getTotalAmountForCurrentMonth":
        echo $sales->getTotalAmountForCurrentMonth();
        break;
    case "getBoughtProductsForThisMonth":
        echo $sales->getBoughtProductsForThisMonth();
        break;
    case "getThisMonthSales":
        echo $sales->getThisMonthSales();
        break;
    case "getLastMonthSales":
        echo $sales->getLastMonthSales();
        break;
    case "getShiftAdminReport":
        echo $sales->getShiftAdminReport($json);
        break;

    // case "getZAllReport":
    //     echo $sales->getZAllReport();
    //     break;
    default:
        echo "Wala kay gi butang nga operation sa ubos HAHAHAHA bobo";
        break;
}