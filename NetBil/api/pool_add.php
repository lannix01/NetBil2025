<?php
require_once('../routeros_api.class.php');

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    $API = new RouterosAPI();
    $API->connect('192.168.1.101', 'lannix', 'lannix123NIC');
    
    $params = [
        'name' => $_POST['name'],
        'ranges' => $_POST['ranges'],
        'next-pool' => $_POST['next-pool'] ?? '',
        'disabled' => $_POST['disabled']
    ];
    
    $result = $API->comm('/ip/pool/add', $params);
    
    $API->disconnect();
    $response['success'] = true;
    $response['message'] = 'IP Pool added successfully';
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>