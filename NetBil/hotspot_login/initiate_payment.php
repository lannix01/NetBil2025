<?php
// initiate_payment.php
require_once 'mpesa_credentials.php';
require_once 'db_connect.php';
require_once 'transaction_functions.php';
require_once 'mpesa_auth.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Generate unique checkout ID
$checkout_id = 'MPESA_' . uniqid();

// Store transaction in database (pseudo-code)
store_transaction([
    'checkout_id' => $checkout_id,
    'phone' => $data['phone'],
    'amount' => $data['amount'],
    'mac' => $data['mac'],
    'package' => $data['package'],
    'status' => 'pending'
]);

// Initiate STK push
$ch = curl_init('https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . get_access_token(),
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . date('YmdHis')),
        'Timestamp' => date('YmdHis'),
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $data['amount'],
        'PartyA' => $data['phone'],
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $data['phone'],
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => $checkout_id,
        'TransactionDesc' => 'Internet Package'
    ]),
    CURLOPT_RETURNTRANSFER => true
]);

$response = json_decode(curl_exec($ch), true);
echo json_encode([
    'success' => isset($response['ResponseCode']) && $response['ResponseCode'] === '0',
    'checkout_id' => $checkout_id,
    'error' => $response['errorMessage'] ?? null
]);