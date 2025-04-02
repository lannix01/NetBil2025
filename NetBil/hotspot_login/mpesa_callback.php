<?php
// mpesa_callback.php
require_once 'mpesa_credentials.php';
require_once 'routeros_api.class.php'; // Your existing RouterOS API class

// Process M-Pesa callback
$response = json_decode(file_get_contents('php://input'), true);

if ($response['Body']['stkCallback']['ResultCode'] === 0) {
    $checkout_id = $response['Body']['stkCallback']['CheckoutRequestID'];
    
    // Extract M-Pesa receipt number
    $mpesa_receipt = null;
    foreach ($response['Body']['stkCallback']['CallbackMetadata']['Item'] as $item) {
        if ($item['Name'] === 'MpesaReceiptNumber') {
            $mpesa_receipt = $item['Value'];
            break;
        }
    }

    // Update transaction in database
    update_transaction($checkout_id, [
        'status' => 'paid',
        'mpesa_receipt' => $mpesa_receipt
    ]);

    // Fetch transaction details
    $transaction = get_transaction($checkout_id);
    if (!$transaction) die("Transaction not found");

    // Connect to MikroTik
    $API = new RouterosAPI();
    try {
        // 1. Connect to MikroTik
        if ($API->connect('192.168.1.101', 'lannix', 'lannix123NIC')) {
            
            // 2. Create Hotspot User
            $API->comm("/ip/hotspot/user/add", [
                "name"     => $transaction['username'],
                "password" => $transaction['password'],
                "profile"  => $transaction['package'],
                "mac-address" => $transaction['mac']
            ]);

            // 3. Disconnect existing sessions for this MAC
            $active_sessions = $API->comm("/ip/hotspot/active/print", [
                "?mac-address" => $transaction['mac']
            ]);
            
            foreach ($active_sessions as $session) {
                $API->comm("/ip/hotspot/active/remove", [
                    ".id" => $session['.id']
                ]);
            }

            $API->disconnect();
            file_put_contents('success.log', "User {$transaction['username']} created\n", FILE_APPEND);
        } else {
            throw new Exception("Failed to connect to MikroTik");
        }
    } catch (Exception $e) {
        file_put_contents('error.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
        die("MikroTik Error: " . $e->getMessage());
    }
}

echo 'OK'; // MPESA expects a response