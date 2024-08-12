<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$productData = [
    ['barcode' => '1001', 'p_name' => 'Spanish Latte', 'price' => 135],
    ['barcode' => '1002', 'p_name' => 'Salted Caramel Latte', 'price' => 145],
    ['barcode' => '1003', 'p_name' => 'Caramel Macchiato', 'price' => 145],
    ['barcode' => '1004', 'p_name' => 'French Vanilla Latte', 'price' => 145],
    ['barcode' => '1005', 'p_name' => 'Cafe Latte', 'price' => 135],
    ['barcode' => '1006', 'p_name' => 'Mocha Latte', 'price' => 145],
    ['barcode' => '1007', 'p_name' => 'Hazelnut Latte', 'price' => 145],
    ['barcode' => '1008', 'p_name' => 'Americano', 'price' => 125],
    ['barcode' => '1009', 'p_name' => 'Haze Mocha Latte', 'price' => 160],
    ['barcode' => '1010', 'p_name' => 'Espresso Tonic', 'price' => 185],


];

$users = [
    ['username' => 'Pitok', 'password' => '12345', 'fullname' => 'Pitok Batolata', 'role' => 'user'],
    ['username' => 'kulas', 'password' => '54321', 'fullname' => 'Kulas de Malas', 'role' => 'user'],
    ['username' => 'sabel', 'password' => '123', 'fullname' => 'Sabel Lach', 'role' => 'user'],
    ['username' => 'admin', 'password' => 'adminpass', 'fullname' => 'Admin User', 'role' => 'admin']
];

$type = $_GET['type'] ?? '';
$password = $_POST['adminPassword'] ?? '';

if ($type === 'products') {
    echo json_encode($productData);
} elseif ($type === 'users') {
    echo json_encode($users);
} elseif ($type === 'verifyAdminPassword') {
    $isValid = false;
    foreach ($users as $user) {
        if ($user['role'] === 'admin' && $user['password'] === $password) {
            $isValid = true;
            break;
        }
    }
    echo json_encode(['isValid' => $isValid]);
} else {
    echo json_encode(['message' => 'Invalid type specified']);
}

?>