<?php
// mpesa_auth.php
function get_access_token() {
    $key = MPESA_CONSUMER_KEY;
    $secret = MPESA_CONSUMER_SECRET;
    
    $ch = curl_init('https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode("$key:$secret")
        ],
        CURLOPT_RETURNTRANSFER => true
    ]);
    
    $response = json_decode(curl_exec($ch), true);
    return $response['access_token'] ?? null;
}