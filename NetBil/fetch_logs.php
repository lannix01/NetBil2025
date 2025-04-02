<?php
header('Content-Type: application/json');

$log_file = 'system_logs.json';

// Initialize system logs if file doesn't exist
if (!file_exists($log_file)) {
    file_put_contents($log_file, json_encode([]));
}

// Handle clear action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    file_put_contents($log_file, json_encode([]));
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

// Handle fetching logs
if (isset($_GET['type'])) {
    if ($_GET['type'] === 'system') {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
        echo json_encode(['success' => true, 'data' => array_reverse($logs)]);
        exit;
    }
    elseif ($_GET['type'] === 'mikrotik') {
        require('routeros_api.class.php');
        $API = new RouterosAPI();
        
        if ($API->connect('192.168.1.101', 'lannix', 'lannix123NIC')) {
            $logs = array_reverse($API->comm('/log/print'));
            $API->disconnect();
            echo json_encode(['success' => true, 'data' => $logs]);
            exit;
        }
    }
}

// Default response
echo json_encode(['success' => false, 'message' => 'Invalid request']);