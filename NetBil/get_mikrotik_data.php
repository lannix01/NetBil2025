<?php
require('routeros_api.class.php');
$API = new RouterosAPI();
$API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

$action = $_GET['action'];

if ($action == 'hotspot_active') {
    $users = $API->comm('/ip/hotspot/active/print');
    echo '<table class="table"><tr><th>User</th><th>IP</th><th>Uptime</th></tr>';
    foreach ($users as $user) {
        echo "<tr><td>{$user['user']}</td><td>{$user['address']}</td><td>{$user['uptime']}</td></tr>";
    }
    echo '</table>';
} 
else if ($action == 'all_users') {
    $users = $API->comm('/ip/hotspot/user/print');
    echo '<table class="table"><tr><th>Name</th><th>Profile</th><th>Limit</th></tr>';
    foreach ($users as $user) {
        echo "<tr><td>{$user['name']}</td><td>{$user['profile']}</td><td>{$user['limit-bytes-total']}</td></tr>";
    }
    echo '</table>';
}

$API->disconnect();
?>