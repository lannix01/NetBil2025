<?php
require('routeros_api.class.php');
$API = new RouterosAPI();
$API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

// Get all DHCP leases
$leases = $API->comm('/ip/dhcp-server/lease/print');
$active_devices = $API->comm('/ip/hotspot/active/print');

$API->disconnect();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>Device Management - BrenNet</title>
    <style>
        .status-card {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            color: white;
        }
        .status-card h5 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .status-card h2 {
            font-size: 24px;
            margin-bottom: 0;
        }
        .chart-container {
            width: 200px;
            height: 200px;
            margin: 0 auto 15px;
        }
        .swal2-popup {
            font-size: 0.9rem !important;
            width: 350px !important;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Device Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back</a></li>
                            <li class="breadcrumb-item active">Devices</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Status Cards Row -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="status-card bg-success">
                            <h5>Active</h5>
                            <h2 id="active-count">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-card bg-secondary">
                            <h5>Offline</h5>
                            <h2 id="offline-count">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-card bg-danger">
                            <h5>Disconnected</h5>
                            <h2 id="disconnected-count">0</h2>
                        </div>
                    </div>
                </div>

                <!-- Donut Chart -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Original Tables -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">DHCP Leases</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>IP Address</th>
                                            <th>MAC Address</th>
                                            <th>Hostname</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leases as $lease): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($lease['address']) ?></td>
                                            <td><?= htmlspecialchars($lease['mac-address']) ?></td>
                                            <td><?= htmlspecialchars($lease['host-name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php if ($lease['status'] === 'bound'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= $lease['status'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Active Hotspot Devices</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>IP Address</th>
                                            <th>MAC Address</th>
                                            <th>Uptime</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($active_devices as $device): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($device['user']) ?></td>
                                            <td><?= htmlspecialchars($device['address']) ?></td>
                                            <td><?= htmlspecialchars($device['mac-address'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($device['uptime']) ?></td>
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

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Initialize status chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Offline', 'Disconnected'],
        datasets: [{
            data: [0, 0, 0],
            backgroundColor: ['#28a745', '#6c757d', '#dc3545'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 10
                }
            }
        }
    }
});

// Calculate and update status counts
function updateStatusCounts() {
    // These values should come from your PHP data
    // For demonstration, using random numbers
    const activeCount = <?= count(array_filter($leases, fn($lease) => $lease['status'] === 'bound')) ?>;
    const offlineCount = <?= count($leases) - count(array_filter($leases, fn($lease) => $lease['status'] === 'bound')) ?>;
    const disconnectedCount = <?= count(array_filter($active_devices, fn($device) => $device['uptime'] === '0s')) ?>;

    // Update cards
    document.getElementById('active-count').textContent = activeCount;
    document.getElementById('offline-count').textContent = offlineCount;
    document.getElementById('disconnected-count').textContent = disconnectedCount;

    // Update chart
    statusChart.data.datasets[0].data = [activeCount, offlineCount, disconnectedCount];
    statusChart.update();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateStatusCounts();
});

// Small error popup example
function showSmallError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        width: '350px'
    });
}
</script>
</body>
</html>