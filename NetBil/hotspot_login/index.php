<?php
// signin.php
session_start();
require_once 'mpesa_credentials.php';
require_once 'db_connect.php';
require_once 'transaction_functions.php';
require_once 'mpesa_auth.php';

// Get client MAC address from MikroTik (ensure your router is configured to send this)
$mac = $_GET['mac'] ?? $_SESSION['mac'] ?? '';
$_SESSION['mac'] = $mac;

// Handle package selection and payment initiation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package = $_POST['package'];
    $price = $_POST['price'];
    
    // Store package selection in session
    $_SESSION['selected_package'] = $package;
    $_SESSION['selected_price'] = $price;
    
    // Redirect to payment initiation
    header('Location: initiate_payment.php');
    exit;
}
?><!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="-1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BrenNet - Select Package</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <meta name="msapplication-TileColor" content="#ef4036">
    <meta name="theme-color" content="#ef4036">
    <style>
        .package-card {
            border: 1px solid #0025a9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .package-card.selected {
            background-color: rgba(0, 37, 169, 0.1);
            border: 2px solid #0025a9;
        }
        .package-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0025a9;
        }
        .package-duration {
            font-size: 1.1rem;
        }
        .package-speed {
            font-size: 0.9rem;
            color: #666;
        }
        #loader {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mx-auto py-4">
                <div class="text-center my-3 mt-5">
                    <img width="250" src="img/logo.png" alt="" class="img-fluid">
                </div>
                <div class="card shadow mt-5" style="border: 1px solid #0025a9;">
                    <div class="card-header">
                        <h6 class="text-center">Select Your Package</h6>
                    </div>
                    <div class="card-body">
                        <!-- Package Selection -->
                        <div class="row" id="packageSelection">
                            <div class="col-md-6">
                                <div class="package-card" onclick="selectPackage('5bob_5min', '5 KSH')">
                                    <div class="package-price">5 KSH</div>
                                    <div class="package-duration">5 Minutes</div>
                                    <div class="package-speed">10Mbps Speed</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="package-card" onclick="selectPackage('10bob_10min', '10 KSH')">
                                    <div class="package-price">10 KSH</div>
                                    <div class="package-duration">10 Minutes</div>
                                    <div class="package-speed">10Mbps Speed</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="package-card" onclick="selectPackage('30bob_30min', '30 KSH')">
                                    <div class="package-price">30 KSH</div>
                                    <div class="package-duration">30 Minutes</div>
                                    <div class="package-speed">6Mbps Speed</div>
                                </div>
                            </div>
                        </div>

                        <!-- Loader (hidden by default) -->
                        <div class="text-center" id="loader" style="transform: rotate(0 deg);">
                            <img width="200" src="wifianim.gif" alt="" loading="lazy" class="img-fluid">
                            <h6 id="pageMessage" style="color: #0025a9; font-weight: bold;" class="mt-3">Processing your request...</h6>
                        </div>

                        <!-- Hidden Forms -->
                        <form name="login" action="/login" method="post" id="loginForm">
                            <input type="hidden" id="lUsername" name="username" />
                            <input type="hidden" id="lPassword" name="password" />
                            <input type="hidden" name="dst" value="https://login.brennet.co.ke/connected" />
                            <input type="hidden" name="popup" value="false" />
                        </form>
                    </div>
                    <div class="card-footer" style="background-color: #0025a9;">
                        <p class="text-center text-light"><small>&copy; 2022 - 2025. All rights reserved.</small></p>
                        <p class="text-center text-light mt-4"><small>Powered by: NetBil<a class="text-light"
                                    href="mailto:nichmu43@gmail.com"></a></small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
async function selectPackage(package, price) {
    const phone = prompt("Enter your M-Pesa phone number (2547XXXXXXXX):");
    if (!phone || !phone.match(/^2547\d{8}$/)) {
        alert("Invalid Safaricom number");
        return;
    }

    showLoader(`Initiating ${price} payment...`);
    
    try {
        const response = await fetch('initiate_payment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                phone: phone,
                amount: price.replace(' KSH', ''),
                package: package,
                mac: '<?= $mac ?>'
            })
        });
        
        const result = await response.json();
        if (result.success) {
            checkPaymentStatus(result.checkout_id);
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        hideLoader();
        alert("Payment failed: " + error.message);
    }
}

function checkPaymentStatus(checkoutId) {
    fetch(`check_payment.php?checkout_id=${checkoutId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'paid') {
            // Auto-login when payment confirmed
            document.getElementById('lUsername').value = data.username;
            document.getElementById('lPassword').value = data.password;
            document.login.submit();
        } else if (data.status === 'pending') {
            setTimeout(() => checkPaymentStatus(checkoutId), 3000);
        } else {
            throw new Error('Payment failed');
        }
    })
    .catch(error => {
        hideLoader();
        alert(error.message);
    });
}
</script>
</body>
</html>