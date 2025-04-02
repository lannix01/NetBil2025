<?php
require_once('../routeros_api.class.php');

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    $API = new RouterosAPI();
    $API->connect('192.168.1.101', 'lannix', 'lannix123NIC');
    
    $params = [
        'mac-address' => $_POST['mac-address'],
        'address' => $_POST['address'],
        'host-name' => $_POST['host-name'] ?? '',
        'server' => $_POST['server'],
        'disabled' => $_POST['disabled']
    ];
    
    $result = $API->comm('/ip/dhcp-server/lease/add', $params);
    
    $API->disconnect();
    $response['success'] = true;
    $response['message'] = 'DHCP Lease added successfully';
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>