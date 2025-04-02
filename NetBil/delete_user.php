<?php
session_start();
require_once 'user_operations.php';

if (!isset($_POST['user_id'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: users.php');
    exit();
}

try {
    $userOps = new UserOperations();
    $userOps->deleteUser($_POST['user_id']);
    $_SESSION['message'] = 'User deleted successfully';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting user: ' . $e->getMessage();
}

header('Location: users.php');
exit();
?>