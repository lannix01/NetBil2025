<?php
// check_payment.php
header('Content-Type: application/json');
$checkout_id = $_GET['checkout_id'];

// Fetch transaction from database
$transaction = get_transaction($checkout_id);

if (!$transaction) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

echo json_encode([
    'status' => $transaction['status'],
    'username' => $transaction['username'] ?? null,
    'password' => $transaction['password'] ?? null
]);