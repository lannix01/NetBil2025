<?php
// transaction_functions.php
require_once 'db_connect.php';

function store_transaction($data) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO transactions 
        (checkout_id, phone, amount, mac, package, status) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['checkout_id'],
        $data['phone'],
        $data['amount'],
        $data['mac'],
        $data['package'],
        'pending'
    ]);
}

function update_transaction($checkout_id, $update_data) {
    global $pdo;
    
    $setParts = [];
    $values = [];
    foreach ($update_data as $key => $value) {
        $setParts[] = "$key = ?";
        $values[] = $value;
    }
    $values[] = $checkout_id;
    
    $stmt = $pdo->prepare("UPDATE transactions SET " 
        . implode(', ', $setParts) 
        . " WHERE checkout_id = ?");
    
    $stmt->execute($values);
}

function get_transaction($checkout_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE checkout_id = ?");
    $stmt->execute([$checkout_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}