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
echo "\n==== Router Info ====\n";
echo "CPU Usage: {$cpu_usage}%\n";
echo "RAM Usage: {$ram_usage}\n";
echo "Storage Usage: {$storage_usage}\n";

echo "\n==== Active Interfaces ====\n";
foreach ($interface_traffic as $iface => $traffic) {
    echo "- {$iface}: RX {$traffic['rx']} bps | TX {$traffic['tx']} bps\n";
}

echo "\n==== Firewall Rules ====\n";
foreach ($firewall_rules as $rule) {
    $chain = $rule['chain'] ?? 'N/A';
    $action = $rule['action'] ?? 'N/A';
    $protocol = $rule['protocol'] ?? 'N/A';
    $dst_port = $rule['dst-port'] ?? 'N/A';
    echo "- Chain: {$chain} | Action: {$action} | Protocol: {$protocol} | Port: {$dst_port}\n";
}

echo "\n==== Routing Table ====\n";
foreach ($routing_table as $route) {
    $dst = $route['dst-address'] ?? 'N/A';
    $gateway = $route['gateway'] ?? 'N/A';
    echo "- Destination: {$dst} | Gateway: {$gateway}\n";
}

echo "\n==== Hotspot Servers ====\n";
foreach ($hotspot_servers as $server) {
    $name = $server['name'] ?? 'N/A';
    $interface = $server['interface'] ?? 'N/A';
    echo "- {$name} on Interface: {$interface}\n";
}

echo "\n==== IP Bindings ====\n";
foreach ($ip_bindings as $binding) {
    $mac = $binding['mac-address'] ?? 'N/A';
    $address = $binding['address'] ?? 'N/A';
    $type = $binding['type'] ?? 'N/A';
    echo "- MAC: {$mac} | Address: {$address} | Type: {$type}\n";
}

// Disconnect API
$API->disconnect();

?>
