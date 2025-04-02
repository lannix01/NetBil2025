<?php
require_once 'mpesa_credentials.php'; // Store credentials separately
require_once 'db_connect.php';
require_once 'transaction_functions.php';
require_once 'mpesa_auth.php';
header('Content-Type: application/json');

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

try {
    // Validate input
    $phone = preg_replace('/[^0-9]/', '', sanitizeInput($_POST['phone']));
    $amount = (float)sanitizeInput($_POST['amount']);
    $profile = sanitizeInput($_POST['profile']);
    
    if (!preg_match('/^254[17]\d{8}$/', $phone)) {
        throw new Exception("Invalid M-Pesa phone number. Use format 2547XXXXXXXX");
    }
    
    if ($amount < 5 || $amount > 100000) {
        throw new Exception("Amount must be between 5 and 100,000 KSH");
    }

    // Generate transaction reference
    $transaction_id = 'HOT' . time() . rand(100, 999);
    
    // Get M-Pesa token
    $token = getMpesaToken();
    if (!$token) {
        throw new Exception("Could not authenticate with M-Pesa");
    }

    // Initiate STK Push
    $stkResponse = initiateSTKPush($token, $phone, $amount, $transaction_id);
    $responseData = json_decode($stkResponse, true);

    if (!isset($responseData['ResponseCode']) || $responseData['ResponseCode'] != "0") {
        throw new Exception($responseData['ResponseDescription'] ?? "M-Pesa request failed");
    }

    // Store transaction
    $transaction = [
        'merchant_request_id' => $responseData['MerchantRequestID'],
        'checkout_request_id' => $responseData['CheckoutRequestID'],
        'phone' => $phone,
        'amount' => $amount,
        'profile' => $profile,
        'status' => 'pending',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents("transactions/{$transaction_id}.json", json_encode($transaction));
    
    // Return success response
    echo json_encode([
        'success' => true,
        'transaction_id' => $transaction_id,
        'checkout_request_id' => $responseData['CheckoutRequestID'],
        'message' => 'Payment request sent to your phone. Complete the prompt on your device.'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getMpesaToken() {
    $credentials = base64_encode(MPESA_CONSUMER_KEY.':'.MPESA_CONSUMER_SECRET);
    
    $ch = curl_init('https://'.MPESA_ENVIRONMENT.'.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic '.$credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response)->access_token ?? null;
}

function initiateSTKPush($token, $phone, $amount, $reference) {
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE.MPESA_PASSKEY.$timestamp);
    
    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => number_format($amount, 2, '.', ''),
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => $reference,
        'TransactionDesc' => 'Hotspot Package Purchase'
    ];
    
    $ch = curl_init('https://'.MPESA_ENVIRONMENT.'.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.$token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}