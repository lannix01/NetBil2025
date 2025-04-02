<?php
// M-Pesa Configuration
define('MPESA_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'production'
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_PASSKEY', 'your_passkey');
define('MPESA_SHORTCODE', 'your_shortcode');
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/mpesa_callback.php');

// Ensure this file is not accessible from web
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

