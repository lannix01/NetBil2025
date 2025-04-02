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
    $API->comm('/ip/dhcp-server/remove', ['.id' => $_POST['id']]);
    jsonResponse(true, 'DHCP Server deleted successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
?>