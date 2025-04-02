<?php
session_start();

// Load admin credentials from JSON file
$adminsFile = 'admins.json';
$admins = [];
if (file_exists($adminsFile)) {
    $jsonContent = file_get_contents($adminsFile);
    $data = json_decode($jsonContent, true);

    if (isset($data['admins']) && is_array($data['admins'])) {
        $admins = $data['admins'];
    } else {
        $error = "Invalid admin credentials file format.";
    }
} else {
    $error = "Admin credentials file not found.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $loggedIn = false;
    foreach ($admins as $admin) {
        if ($admin['username'] === $username && $admin['password'] === $password) {
            $_SESSION['loggedin'] = true;
            $loggedIn = true;
            break;
        }
    }

    if ($loggedIn) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netbil login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-box {
            max-width: 400px;
            margin: auto;
            padding-top: 10vh;
        }

        .card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .card { height: 600px; }

        .card-header {
            background: rgb(255, 255, 255);
            border-bottom: none;
            text-align: center;
        }

        .logo {
            width: 280px;
            transition: transform 0.3s ease;
        }

        .card-body {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 1rem;
            border: 2px solidrgb(211, 79, 79);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1);
        }

        .btn-primary {
            background: #764ba2;
            border: none;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #667eea;
            transform: translateY(-2px);
        }

        .show-password {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>
<div class="login-box">
    <div class="card">
        <div class="card-header">
            <img src="logo.png" alt=" Logo" class="logo">
            <p class="text-muted">Sign in to start your session</p>
        </div>
        
        <div class="card-body">
            <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form action="index.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="bi bi-person fs-5 text-primary"></i>
                        </span>
                        <input type="text" 
                               name="username" 
                               id="username" 
                               class="form-control"
                               placeholder="Enter your username"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="bi bi-lock fs-5 text-primary"></i>
                        </span>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="form-control"
                               placeholder="Password required"
                               required>
                    </div>
                    <div class="mt-2">
                        <input type="checkbox" id="showPassword" class="form-check-input">
                        <label for="showPassword" class="form-check-label show-password">Show Password</label>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <a href="https://wa.me/+254727248598" class="text-decoration-none text-primary">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Show Password Toggle
    document.getElementById('showPassword').addEventListener('change', function() {
        const passwordField = document.getElementById('password');
        passwordField.type = this.checked ? 'text' : 'password';
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
