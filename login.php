<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow specific HTTP methods
header('Access-Control-Allow-Headers: Content-Type'); // Allow specific headers

// $users = [
//     ['username' => 'Pitok', 'password' => '12345', 'fullname' => 'Pitok Batolata', 'role' => 'user'],
//     ['username' => 'kulas', 'password' => '54321', 'fullname' => 'Kulas de Malas', 'role' => 'user'],
//     ['username' => 'sabel', 'password' => '123', 'fullname' => 'Sabel Lach', 'role' => 'user'],
//     ['username' => 'admin', 'password' => 'adminpass', 'fullname' => 'Admin User', 'role' => 'admin']
// ];

$transactions = [
    ['username' => 'Pitok', 'transaction' => 'Bought item A'],
    ['username' => 'kulas', 'transaction' => 'Bought item B'],
    ['username' => 'sabel', 'transaction' => 'Bought item C']
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? '';

    if ($type === 'users') {
        echo json_encode($users);
    } elseif ($type === 'transactions') {
        echo json_encode($transactions);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['username'], $data['password'], $data['fullname'], $data['role'])) {
        $newUser = [
            'username' => $data['username'],
            'password' => $data['password'],
            'fullname' => $data['fullname'],
            'role' => $data['role']
        ];

        // Simulate adding user to database
        $users[] = $newUser;
        echo json_encode(['message' => 'User added successfully']);
    } else {
        echo json_encode(['message' => 'Invalid data']);
    }
}
?>