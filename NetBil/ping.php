<?php
// ping.php
require_once 'routeros_api.class.php';

$API = new RouterosAPI();
$API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

// Execute ping command
$response = $API->comm('/ping', [
    'address' => '8.8.8.8',
    'count' => 4
]);

header('Content-Type: application/json');
echo json_encode([
    'success' => isset($response[0]['received']) && $response[0]['received'] > 0,
    'received' => $response[0]['received'] ?? 0
]);

$API->disconnect();