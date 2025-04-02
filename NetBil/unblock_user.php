<?php
require('routeros_api.class.php');
$API = new RouterosAPI();
$API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

$API->comm('/ip/hotspot/ip-binding/remove', [
    '.id' => $_POST['id']
]);

$API->disconnect();
echo json_encode(['status' => 'success']);
?>