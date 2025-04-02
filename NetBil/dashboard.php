<?php
require('routeros_api.class.php');
$API = new RouterosAPI();

// Try to connect to the router
$connection_success = $API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

if (!$connection_success) {
    $connection_error = 'Failed to connect to Mikrotik API';
} else {
    // Fetch all data
    $router_info = $API->comm('/system/resource/print');
    $interfaces = $API->comm('/interface/print');
    $hotspot_users = $API->comm('/ip/hotspot/user/print');
    $hotspot_active = $API->comm('/ip/hotspot/active/print');
    $hotspot_hosts = $API->comm('/ip/hotspot/host/print');
    $ethernet_stats = $API->comm('/interface ethernet print stats');
    
    // Process system status
    $cpu_usage = $router_info[0]['cpu-load'] ?? 'N/A';
    $total_ram = round(($router_info[0]['total-memory'] ?? 0) / (1024 * 1024), 2);
    $free_ram = round(($router_info[0]['free-memory'] ?? 0) / (1024 * 1024), 2);
    $ram_percentage = $total_ram > 0 ? round(($free_ram / $total_ram) * 100, 2) : 0;
    $ram_usage = "$free_ram MB / $total_ram MB ($ram_percentage%)";
    
    $total_hdd = round(($router_info[0]['total-hdd-space'] ?? 0) / (1024 * 1024 * 1024), 2);
    $free_hdd = round(($router_info[0]['free-hdd-space'] ?? 0) / (1024 * 1024 * 1024), 2);
    $hdd_percentage = $total_hdd > 0 ? round(($free_hdd / $total_hdd) * 100, 2) : 0;
    $storage_usage = "$free_hdd GB / $total_hdd GB ($hdd_percentage%)";

    // Process interface traffic
    $interface_traffic = [];
    foreach ($interfaces as $interface) {
        if (($interface['running'] ?? '') == 'true') {
            $traffic = $API->comm('/interface/monitor-traffic', [
                'interface' => $interface['name'],
                'once' => '',
            ]);
            $interface_traffic[$interface['name']] = [
                'rx' => $traffic[0]['rx-bits-per-second'] ?? 0,
                'tx' => $traffic[0]['tx-bits-per-second'] ?? 0,
            ];
        }
    }
    
    $active_users_count = count($hotspot_active);
    $total_users_count = count($hotspot_users);
    
    $API->disconnect();
}

// Helper functions
function formatBits($bits) {
    if ($bits >= 1000000000) return round($bits/1000000000, 2).' Gbps';
    if ($bits >= 1000000) return round($bits/1000000, 2).' Mbps';
    if ($bits >= 1000) return round($bits/1000, 2).' Kbps';
    return $bits.' bps';
}
?>

<?php include 'header.php'; ?>

<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if (isset($connection_error)): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?= htmlspecialchars($connection_error) ?>
                </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?= $active_users_count ?? 'N/A' ?></h3>
                                <p>Online Users</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <a href="users.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= round(($active_users_count/max($total_users_count,1))*100) ?? 0 ?><sup>%</sup></h3>
                                <p>Subscription Rate</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <a href="reports.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $total_users_count ?? 'N/A' ?></h3>
                                <p>Total Users</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                            <a href="users.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= count($hotspot_hosts ?? []) ?? 'N/A' ?></h3>
                                <p>Unique Visitors</p>
                            </div>
                            <div class="icon">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <a href="reports.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
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
                                        <p><b>RAM Usage:</b> <?= $ram_usage ?? 'N/A' ?></p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" style="width: <?= $ram_percentage ?? 0 ?>%"></div>
                                        </div>
                                        
                                        <p><b>CPU Usage:</b> <?= $cpu_usage ?? 'N/A' ?>%</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-info" style="width: <?= $cpu_usage ?? 0 ?>%"></div>
                                        </div>
                                        
                                        <p><b>Storage Usage:</b> <?= $storage_usage ?? 'N/A' ?></p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-warning" style="width: <?= $hdd_percentage ?? 0 ?>%"></div>
                                        </div>
                                        
                                        <p><b>Uptime:</b> <?= $router_info[0]['uptime'] ?? 'N/A' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Router Info Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Mikrotik Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>R.B Name</th>
                                        <td><?= htmlspecialchars($router_info[0]['board-name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Model</th>
                                        <td><?= htmlspecialchars($router_info[0]['board'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Firmware</th>
                                        <td><?= htmlspecialchars($router_info[0]['version'] ?? 'N/A') ?></td>
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
                                        <?php foreach ($interface_traffic ?? [] as $name => $traffic): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($name) ?></td>
                                            <td class="traffic-value"><?= formatBits($traffic['rx']) ?></td>
                                            <td class="traffic-value"><?= formatBits($traffic['tx']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Active Users Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Active Users (<?= $active_users_count ?? 0 ?>)</h3>
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
                                        <?php foreach ($hotspot_active ?? [] as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['user'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($user['address'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($user['uptime'] ?? '') ?></td>
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

    <?php include 'footer.php'; ?>
</div>