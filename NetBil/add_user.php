<?php
session_start();
require_once 'user_operations.php';

try {
    $userOps = new UserOperations();
    $userOps->addUser($_POST);
    $_SESSION['message'] = 'User added successfully';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error adding user: ' . $e->getMessage();
}

header('Location: users.php');
exit();
?>