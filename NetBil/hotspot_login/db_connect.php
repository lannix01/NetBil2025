<?php
$host = 'sql300.infinityfree.com';
$db   = 'if0_38629736_netbildb';
$user = 'if0_38629736';
$pass = 'lannix123NIC';
$port = '3306'; // Default MySQL port

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<script>console.log('Database connection successful!');</script>";
    echo "<!-- Connection successful -->";
    
} catch (PDOException $e) {
    $error = 'Database connection failed: ' . $e->getMessage();
    echo "<script>console.error('$error');</script>";
    echo "<pre style='color:red'>$error</pre>";
    
    // Additional troubleshooting info
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>
        <li>This script MUST run on InfinityFree's servers</li>
        <li>Verify credentials in InfinityFree's control panel</li>
        <li>Check if MySQL is enabled for your account</li>
    </ol>";
}