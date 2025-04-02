<?php
session_start();
require_once 'user_operations.php';

if (!isset($_POST['user_id']) || !isset($_POST['username'])) {
    $_SESSION['error'] = 'Invalid request parameters';
    header('Location: users.php');
    exit();
}

try {
    $userOps = new UserOperations();
    
    // Get existing user data first 🔧
    $userId = $_POST['user_id'];
    $userData = [
        'password' => $_POST['password'] ?? null,
        'profile' => $_POST['profile'] ?? 'default',
        'limit' => $_POST['limit'] ?? null
    ];
    
    $userOps->updateUser($userId, $userData);
    $_SESSION['message'] = 'User updated successfully';
} catch (Exception $e) {
    $_SESSION['error'] = 'Update failed: ' . $e->getMessage();
}

header('Location: users.php');
exit();