<?php
require_once('../routeros_api.class.php');

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    $API = new RouterosAPI();
    $API->connect('192.168.1.101', 'lannix', 'lannix123NIC');
    
    $params = [
        'interface' => $_POST['interface'],
        'add-default-route' => $_POST['add-default-route'],
        'use-peer-dns' => $_POST['use-peer-dns'],
        'disabled' => $_POST['disabled']
    ];
    
    $result = $API->comm('/ip/dhcp-client/add', $params);
    
    $API->disconnect();
    $response['success'] = true;
    $response['message'] = 'DHCP Client added successfully';
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>