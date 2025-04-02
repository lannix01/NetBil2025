<?php
// Define API credentials and router IP
$router_ip = '192.168.1.101'; // Mikrotik router IP
$api_username = 'lannix'; // Mikrotik API username
$api_password = 'lannix123NIC'; // Mikrotik API password

// Establish API connection
require('routeros_api.class.php');
$API = new RouterosAPI();

// Try to connect to the router
$connection_success = $API->connect($router_ip, $api_username, $api_password);

// If connection fails, show error but don't die (we'll handle it in the HTML)
if (!$connection_success) {
    $connection_error = 'Failed to connect to Mikrotik API';
}

// Fetch data only if connection succeeded
if ($connection_success) {
    // Fetch all data
    $router_info = $API->comm('/system/resource/print');
    $interfaces = $API->comm('/interface/print');
    $hotspot_users = $API->comm('/ip/hotspot/user/print');
    $hotspot_active = $API->comm('/ip/hotspot/active/print');
    $hotspot_hosts = $API->comm('/ip/hotspot/host/print');
    $routing_table = $API->comm('/ip/route/print');
    $firewall_rules = $API->comm('/ip/firewall/filter/print');
    $hotspot_servers = $API->comm('/ip/hotspot/print');
    $ip_bindings = $API->comm('/ip/hotspot/ip-binding/print');

    // Process system status with proper formatting
    $cpu_usage = $router_info[0]['cpu-load'] ?? 'N/A';
    
    // Format RAM (convert bytes to MB/GB)
    $total_ram = round(($router_info[0]['total-memory'] ?? 0) / (1024 * 1024), 2);
    $free_ram = round(($router_info[0]['free-memory'] ?? 0) / (1024 * 1024), 2);
    $ram_percentage = $total_ram > 0 ? round(($free_ram / $total_ram) * 100, 2) : 0;
    $ram_usage = "$free_ram MB / $total_ram MB ($ram_percentage%)";
    
    // Format Storage (convert bytes to GB)
    $total_hdd = round(($router_info[0]['total-hdd-space'] ?? 0) / (1024 * 1024 * 1024), 2);
    $free_hdd = round(($router_info[0]['free-hdd-space'] ?? 0) / (1024 * 1024 * 1024), 2);
    $hdd_percentage = $total_hdd > 0 ? round(($free_hdd / $total_hdd) * 100, 2) : 0;
    $storage_usage = "$free_hdd GB / $total_hdd GB ($hdd_percentage%)";

    // Process active interfaces and their TX/RX
    $interface_traffic = [];
    // Filter only active interfaces
    // Filter only active interfaces using an arrow function
    $active_interfaces = array_filter($interfaces, fn($interface) => ($interface['running'] ?? '') == 'true');


    // Cache traffic data for active interfaces
    // Fetch traffic data for active interfaces
    // 'once' => '' ensures the command runs only once and does not keep monitoring
    // 'name' => array_column($active_interfaces, 'name') specifies the interfaces to monitor
    $traffic_data = $API->comm('/interface/monitor-traffic', [
        'once' => '',
        'name' => array_column($active_interfaces, 'name'),
    ]);

    $active_interfaces = array_slice($active_interfaces, 0, 10);
    foreach ($active_interfaces as $interface) {
        foreach ($traffic_data as $traffic) {
            if ($traffic['name'] === $interface['name']) {
                $interface_traffic[$interface['name']] = [
                    'rx' => $traffic['rx-bits-per-second'] ?? 0,
                    'tx' => $traffic['tx-bits-per-second'] ?? 0,
                ];
                break;
            }
        }
        }

    // Helper functions

    /**
     * Check if an interface is running.
     *
     * @param array $interface The interface data.
     * @return bool True if the interface is running, false otherwise.
     */

    $active_users_count = count($hotspot_active);
    $total_users_count = count($hotspot_users);
    
    // Disconnect API
    $API->disconnect();
}

// Helper functions
function formatBits($bits) {
    if ($bits >= 1000000000) return round($bits/1000000000, 2).' Gbps';
    if ($bits >= 1000000) return round($bits/1000000, 2).' Mbps';
    if ($bits >= 1000) return round($bits/1000, 2).' Kbps';
    return $bits.' bps';
}

function formatBytes($bytes) {
    if ($bytes >= 1099511627776) return round($bytes/1099511627776, 2).' TB';
    if ($bytes >= 1073741824) return round($bytes/1073741824, 2).' GB';
    if ($bytes >= 1048576) return round($bytes/1048576, 2).' MB';
    if ($bytes >= 1024) return round($bytes/1024, 2).' KB';
    return $bytes.' B';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrenNet Dashboard</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <style>
        .clickable-card { cursor: pointer; transition: transform 0.2s; }
        .clickable-card:hover { transform: translateY(-5px); }
        .traffic-value { font-family: monospace; }
        .main-sidebar { position: fixed; }
        .content-wrapper { margin-left: 250px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown (Logs) -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-danger navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">3 Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-exclamation-triangle mr-2"></i> System alert
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li>
            
            <!-- User Account Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                    <img src="profile.jpg" class="rounded-circle" width="25" height="25">
                    <span class="d-none d-md-inline">Admin</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-person mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="login.php" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right mr-2"></i> Logout
                    </a>
                    <form id="logout-form" action="logout.php" method="POST" style="display: none;">
                        <input type="hidden" name="_token" value="">
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="dashboard.php" class="brand-link">
            <img src="logo.png" class="brand-image img-circle elevation-3">
            <span class="brand-text font-weight-light">BrenNet</span>
        </a>

        <!-- Sidebar Menu -->
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
                    <!-- Users -->
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    
                    <!-- Devices -->
                    <li class="nav-item">
                        <a href="devices.php" class="nav-link">
                            <i class="nav-icon bi bi-laptop"></i>
                            <p>Devices</p>
                        </a>
                    </li>
                    
                    <!-- IP/DHCP Pools -->
                    <li class="nav-item">
                        <a href="dhcp.php" class="nav-link">
                            <i class="nav-icon bi bi-hdd-stack"></i>
                            <p>IP/DHCP Pools</p>
                        </a>
                    </li>
                    
                    <!-- Reports -->
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link">
                            <i class="nav-icon bi bi-graph-up"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <?php if (isset($connection_error)): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($connection_error); ?>
                </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary clickable-card" onclick="showUserTable('active')">
                            <div class="inner">
                                <h3><?php echo $active_users_count ?? 'N/A'; ?></h3>
                                <p>Online Users</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo round(($active_users_count/max($total_users_count,1))*100) ?? 0; ?><sup>%</sup></h3>
                                <p>Subscription Rate</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning clickable-card" onclick="showUserTable('all')">
                            <div class="inner">
                                <h3><?php echo $total_users_count ?? 'N/A'; ?></h3>
                                <p>Total Users</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo count($hotspot_hosts ?? []) ?? 'N/A'; ?></h3>
                                <p>Unique Visitors</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">System Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p><b>RAM Usage:</b> <?php echo $ram_usage; ?></p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: <?php echo $ram_percentage; ?>%"></div>
                                        </div>
                                        
                                        <p><b>CPU Usage:</b> <?php echo $cpu_usage; ?>%</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-info" style="width: <?php echo $cpu_usage; ?>%"></div>
                                        </div>
                                        
                                        <p><b>Storage Usage:</b> <?php echo $storage_usage; ?></p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $hdd_percentage; ?>%"></div>
                                        </div>
                                        
                                        <p><b>Uptime:</b> <?php echo $router_info[0]['uptime'] ?? 'N/A'; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Router Info Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Router Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Router Name</th>
                                        <td><?php echo htmlspecialchars($router_info[0]['board-name'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Model</th>
                                        <td><?php echo htmlspecialchars($router_info[0]['board'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Firmware</th>
                                        <td><?php echo htmlspecialchars($router_info[0]['version'] ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Interface Traffic Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Interface Traffic</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Interface</th>
                                            <th>Download (RX)</th>
                                            <th>Upload (TX)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($interface_traffic as $name => $traffic): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($name); ?></td>
                                            <td class="traffic-value"><?php echo formatBits($traffic['rx']); ?></td>
                                            <td class="traffic-value"><?php echo formatBits($traffic['tx']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Active Users Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Active Users (<?php echo $active_users_count; ?>)</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>IP</th>
                                            <th>Uptime</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hotspot_active as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['user'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($user['address'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($user['uptime'] ?? ''); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2025 <a href="#">BrenNet</a>.</strong>
        All rights reserved.
    </footer>
</div>

<!-- Required Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/js/adminlte.min.js"></script>

<script>


// Show user tables when cards are clicked
function showUserTable(type) {
    // Implementation would go here
    alert('Showing ' + type + ' users');
}
</script>
</body> 
</html>
