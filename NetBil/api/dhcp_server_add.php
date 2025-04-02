<?php
require_once('auth.php');

if (!isAjaxRequest() || $_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCsrfToken($_POST['csrf_token'])) {
    jsonResponse(false, 'Invalid request');
}

try {
    $required = ['name', 'interface', 'address-pool', 'lease-time'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            jsonResponse(false, "Missing required field: $field");
        }
    }

    $API = getApiConnection();
    $params = [
        'name' => $_POST['name'],
        'interface' => $_POST['interface'],
        'address-pool' => $_POST['address-pool'],
        'lease-time' => $_POST['lease-time'],
        'disabled' => $_POST['disabled'] ?? 'no',
        'comment' => $_POST['comment'] ?? ''
    ];

    $result = $API->comm('/ip/dhcp-server/add', $params);
    jsonResponse(true, 'DHCP Server added successfully', ['id' => $result]);

} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
?>