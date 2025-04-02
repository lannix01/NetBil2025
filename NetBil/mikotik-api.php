<?php

// Define API credentials and router IP
$router_ip = '192.168.1.101'; // Mikrotik router IP
$api_username = 'lannix'; // Mikrotik API username
$api_password = 'lannix123NIC'; // Mikrotik API password

// Establish API connection
require('routeros_api.class.php');
$API = new RouterosAPI();

if (!$API->connect($router_ip, $api_username, $api_password)) {
    die('Failed to connect to Mikrotik API');
}

// Fetch all data
$router_info = $API->comm('/system/resource/print');
$interfaces = $API->comm('/interface/print');
$hotspot_users = $API->comm('/ip/hotspot/user/print');
$hotspot_active = $API->comm('/ip/hotspot/active/print');
$hotspot_hosts = $API->comm('/ip/hotspot/host/print');
$routing_table = $API->comm('/ip/route/print');
$firewall_rules = $API->comm('/ip/firewall/filter/print');
$hotspot_servers = $API->comm('/ip/hotspot/print');
$ip_bindings = $API->comm('/ip/hotspot/ip-binding/print');

// Fetch system status
$cpu_usage = $router_info[0]['cpu-load'];
$ram_usage = $router_info[0]['free-memory'] . ' / ' . $router_info[0]['total-memory'];
$storage_usage = $router_info[0]['free-hdd-space'] . ' / ' . $router_info[0]['total-hdd-space'];

// Fetch active interfaces and their TX/RX
$interface_traffic = [];
foreach ($interfaces as $interface) {
    if ($interface['running'] == 'true') {
        $traffic = $API->comm('/interface/monitor-traffic', [
            'interface' => $interface['name'],
            'once' => '',
        ]);
        $interface_traffic[$interface['name']] = [
            'rx' => $traffic[0]['rx-bits-per-second'] ?? 0,
            'tx' => $traffic[0]['tx-bits-per-second'] ?? 0,
        ];
    }
}

// Fetch TX/RX for hotspot active users
$hotspot_traffic = [];
foreach ($hotspot_active as $user) {
    $traffic = $API->comm('/queue/simple/print', [
        '?target' => $user['address'],
    ]);
    $hotspot_traffic[$user['user']] = [
        'rx' => $traffic[0]['rate-down'] ?? 0,
        'tx' => $traffic[0]['rate-up'] ?? 0,
    ];
}

// Output results
$output = [
    'router_info' => $router_info,
    'interfaces' => $interfaces,
    'hotspot_users' => $hotspot_users,
    'hotspot_active' => $hotspot_active,
    'hotspot_traffic' => $hotspot_traffic,
    'routing_table' => $routing_table,
    'firewall_rules' => $firewall_rules,
    'hotspot_servers' => $hotspot_servers,
    'interface_traffic' => $interface_traffic
];

header('Content-Type: application/json');
echo json_encode($output);

// Disconnect API
$API->disconnect();

?>
