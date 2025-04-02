<?php
require_once('../routeros_api.class.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Protection
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// API Connection
function getApiConnection() {
    static $API = null;
    if ($API === null) {
        $API = new RouterosAPI();
        if (!$API->connect('192.168.1.101', 'lannix', 'lannix123NIC')) {
            throw new Exception('Failed to connect to RouterOS API');
        }
    }
    return $API;
}

// Input Validation
function validateMacAddress($mac) {
    return preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $mac);
}

function validateIpAddress($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP);
}

// Standard JSON Response
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>