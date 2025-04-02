<?php
require('routeros_api.class.php');
$API = new RouterosAPI();
$API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

if (isset($_POST['disconnect'])) {
    $sessionId = $_POST['session_id'];
    $API->comm('/ip/hotspot/active/remove', array('.id' => $sessionId));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['user_traffic'])) {
    $userIP = $_GET['ip'];
    $sessionId = $_GET['session_id'];
    
    $traffic = $API->comm('/interface/monitor-traffic', array(
        'interface' => 'bridge-hotspot',
        'address' => $userIP,
        'once' => ''
    ));
    
    header('Content-Type: application/json');
    echo json_encode([
        'time' => date("H:i:s"),
        'rx_bits' => isset($traffic[0]['rx-bits-per-second']) ? intval($traffic[0]['rx-bits-per-second']) : 0,
        'tx_bits' => isset($traffic[0]['tx-bits-per-second']) ? intval($traffic[0]['tx-bits-per-second']) : 0
    ]);
    exit;
}

$leases = $API->comm('/ip/dhcp-server/lease/print');
$active_devices = $API->comm('/ip/hotspot/active/print');
$API->disconnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>Device Management - NetBil</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .traffic-container { 
            display: flex; 
            gap: 20px;
            margin-bottom: 15px;
        }
        .chart-wrapper {
            width: 200px;
            height: 50px;
            position: relative;
        }
        .traffic-chart {
            width: 200px !important;
            height: 50px !important;
        }
        .current-rate {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 5px 10px;
            font-family: monospace;
            text-align: center;
            margin-top: 5px;
        }
        .rate-badge { 
            font-size: 0.8rem; 
            padding: 3px 6px;
        }
        .user-traffic { 
            cursor: pointer;
            color: #007bff;
            font-weight: 500;
        }
        .user-traffic:hover {
            text-decoration: underline;
        }
        .modal-header .btn-refresh {
            margin-left: 10px;
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }
        .modal-header .btn-refresh:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .modal-header .close {
            color: #dc3545;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .btn-disconnect {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .chart-label {
            font-size: 0.9rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            color: #495057;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="modal fade" id="trafficModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Live Traffic: <span id="userName" class="font-weight-bold"></span></h5>
                    <div>
                        <button type="button" class="btn btn-refresh btn-sm">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="close ml-2" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body p-3">
                    <div class="traffic-container">
                        <div>
                            <div class="chart-label">Download/Upload (Mbps)</div>
                            <div class="chart-wrapper">
                                <canvas id="bytesChart" class="traffic-chart"></canvas>
                            </div>
                            <div class="current-rate">
                                <span class="rate-badge badge bg-primary">Rx: <span id="currentRxRate">0</span> Mbps</span>
                                <span class="rate-badge badge bg-success ml-2">Tx: <span id="currentTxRate">0</span> Mbps</span>
                            </div>
                        </div>
                        <div>
                            <div class="chart-label">Download/Upload (Kbps)</div>
                            <div class="chart-wrapper">
                                <canvas id="packetsChart" class="traffic-chart"></canvas>
                            </div>
                            <div class="current-rate">
                                <span class="rate-badge badge bg-warning text-dark">Rx: <span id="currentRxPackets">0</span> Kbps</span>
                                <span class="rate-badge badge bg-danger ml-2">Tx: <span id="currentTxPackets">0</span> Kbps</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Device Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Devices</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
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
                                                <span class="badge <?= ($lease['status'] === 'bound') ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $lease['status'] ?>
                                                </span>
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
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($active_devices as $device): ?>
                                        <tr>
                                            <td class="user-traffic"
                                                data-ip="<?= htmlspecialchars($device['address']) ?>"
                                                data-user="<?= htmlspecialchars($device['user']) ?>"
                                                data-session="<?= htmlspecialchars($device['.id']) ?>">
                                                <i class="fas fa-user-circle mr-1"></i> <?= htmlspecialchars($device['user']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($device['address']) ?></td>
                                            <td><?= htmlspecialchars($device['mac-address'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($device['uptime']) ?></td>
                                            <td>
                                                <form method="post" onsubmit="return confirm('Disconnect this user?');">
                                                    <input type="hidden" name="session_id" value="<?= htmlspecialchars($device['.id']) ?>">
                                                    <button type="submit" name="disconnect" class="btn btn-danger btn-sm btn-disconnect" title="Disconnect">
                                                        <i class="fas fa-plug"></i> Disconnect
                                                    </button>
                                                </form>
                                            </td>
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

<script>
let bytesChart, packetsChart;
let updateInterval;
let currentIp = '';
let currentSessionId = '';
let isFirstLoad = true;

function initChart(ctx, label1, color1, label2, color2, isMbps) {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: label1,
                    data: [],
                    borderColor: color1,
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.1,
                    fill: false
                },
                {
                    label: label2,
                    data: [],
                    borderColor: color2,
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.1,
                    fill: false
                }
            ]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            plugins: { legend: { display: false } },
            scales: {
                x: { 
                    display: false,
                    grid: { display: false }
                },
                y: {
                    display: false,
                    beginAtZero: true,
                    max: isMbps ? 20 : 1000,
                    grid: { display: false }
                }
            },
            elements: {
                line: {
                    borderJoinStyle: 'round'
                }
            }
        }
    });
}

function updateChart(chart, time, rxValue, txValue) {
    if (chart.data.labels.length >= 30) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
        chart.data.datasets[1].data.shift();
    }
    
    chart.data.labels.push(time);
    chart.data.datasets[0].data.push(rxValue);
    chart.data.datasets[1].data.push(txValue);
    
    // Auto-scale y-axis based on current max value
    const maxValue = Math.max(...chart.data.datasets[0].data, ...chart.data.datasets[1].data);
    chart.options.scales.y.max = Math.max(maxValue * 1.2, chart.options.scales.y.max);
    
    chart.update();
}

function fetchTrafficData() {
    if (!currentIp || !currentSessionId) return;
    
    fetch(`?user_traffic=1&ip=${currentIp}&session_id=${currentSessionId}&t=${new Date().getTime()}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            const time = new Date().toLocaleTimeString();
            const rxMbps = (data.rx_bits / 1e6).toFixed(2);
            const txMbps = (data.tx_bits / 1e6).toFixed(2);
            const rxKbps = (data.rx_bits / 1e3).toFixed(2);
            const txKbps = (data.tx_bits / 1e3).toFixed(2);
            
            updateChart(bytesChart, time, rxMbps, txMbps);
            updateChart(packetsChart, time, rxKbps, txKbps);
            
            document.getElementById('currentRxRate').textContent = rxMbps;
            document.getElementById('currentTxRate').textContent = txMbps;
            document.getElementById('currentRxPackets').textContent = rxKbps;
            document.getElementById('currentTxPackets').textContent = txKbps;
            
            // Update last updated time
            document.getElementById('lastUpdateTime').textContent = time;
        })
        .catch(error => {
            console.error('Error fetching traffic data:', error);
            // Optionally show error to user
        });
}

function startLiveUpdates() {
    // Clear any existing interval
    if (updateInterval) clearInterval(updateInterval);
    
    // Fetch immediately
    fetchTrafficData();
    
    // Then set up regular updates
    updateInterval = setInterval(fetchTrafficData, 1000);
}

function resetCharts() {
    if (bytesChart) {
        bytesChart.data.labels = [];
        bytesChart.data.datasets.forEach(dataset => dataset.data = []);
        bytesChart.update();
    }
    if (packetsChart) {
        packetsChart.data.labels = [];
        packetsChart.data.datasets.forEach(dataset => dataset.data = []);
        packetsChart.update();
    }
}

$(document).ready(function() {
    // Initialize charts when modal opens
    $('#trafficModal').on('shown.bs.modal', function() {
        const bytesCtx = document.getElementById('bytesChart').getContext('2d');
        const packetsCtx = document.getElementById('packetsChart').getContext('2d');
        
        // Set explicit dimensions
        document.getElementById('bytesChart').width = 200;
        document.getElementById('bytesChart').height = 50;
        document.getElementById('packetsChart').width = 200;
        document.getElementById('packetsChart').height = 50;
        
        // Destroy existing charts if they exist
        if (bytesChart) bytesChart.destroy();
        if (packetsChart) packetsChart.destroy();
        
        bytesChart = initChart(bytesCtx, 'Download', '#4e73df', 'Upload', '#1cc88a', true);
        packetsChart = initChart(packetsCtx, 'Download', '#f6c23e', 'Upload', '#e74a3b', false);
        
        // Start live updates
        startLiveUpdates();
    });

    // User click handler
    $('.user-traffic').click(function() {
        currentIp = $(this).data('ip');
        const user = $(this).data('user');
        currentSessionId = $(this).data('session');
        
        $('#userName').text(user);
        $('#trafficModal').modal('show');
    });

    // Refresh button handler
    $('.btn-refresh').click(function() {
        const $icon = $(this).find('i');
        $icon.addClass('fa-spin');
        
        resetCharts();
        fetchTrafficData();
        
        setTimeout(() => {
            $icon.removeClass('fa-spin');
        }, 1000);
    });

    // Clean up when modal closes
    $('#trafficModal').on('hidden.bs.modal', function() {
        clearInterval(updateInterval);
        currentIp = '';
        currentSessionId = '';
    });
});
</script>
</body>
</html>