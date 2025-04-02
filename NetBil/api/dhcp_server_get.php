<?php
require_once('auth.php');

if (!isAjaxRequest() || $_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCsrfToken($_POST['csrf_token'])) {
    jsonResponse(false, 'Invalid request');
}

try {
    if (empty($_POST['id'])) {
        jsonResponse(false, 'Missing server ID');
    }

    $API = getApiConnection();
    $server = $API->comm('/ip/dhcp-server/print', [
        '.id' => $_POST['id']
    ]);
    
    if (empty($server)) {
        jsonResponse(false, 'DHCP Server not found');
    }
    
    jsonResponse(true, '', $server[0]);

} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
?>