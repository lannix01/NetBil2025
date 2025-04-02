<?php
require('routeros_api.class.php');
$API = new RouterosAPI();

// Try to connect to the router
$connection_success = $API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

if (!$connection_success) {
    $connection_error = 'Failed to connect to Mikrotik API';
} else {
    // Fetch Mikrotik logs (newest first)
    $mikrotik_logs = array_reverse($API->comm('/log/print'));
    $API->disconnect();
}

// Fetch system logs from JSON file (newest first)
$system_logs = [];
$log_file = 'system_logs.json';
if (file_exists($log_file)) {
    $system_logs = array_reverse(json_decode(file_get_contents($log_file), true) ?: []);
}

// Function to format timestamp
function formatLogTime($timestamp) {
    if (empty($timestamp)) {
        return 'N/A';
    }
    try {
        $date = new DateTime($timestamp);
        return $date->format('H:i:s');
    } catch (Exception $e) {
        return 'Invalid';
    }
}

include 'header.php';
?>

<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>System Logs <small class="text-muted">mikrotik</small></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="reports.php">Reports</a></li>
                            <li class="breadcrumb-item active">Logs</li>
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
                
                <div class="row">
                    <!-- Mikrotik Logs Card -->
                    <div class="col-md-6">
                        <div class="card card-dark">
                            <div class="card-header border-0">
                                <h3 class="card-title">
                                    <i class="fas fa-network-wired"></i> Mikrotik Logs
                                    <span class="badge bg-secondary float-right" id="mikrotik-count"><?= count($mikrotik_logs ?? []) ?></span>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" onclick="refreshLogs('mikrotik')" title="Refresh">🔄️
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0 console-container">
                                <div class="console-log" id="mikrotik-logs">
                                    <?php foreach (array_slice($mikrotik_logs ?? [], 0, 100) as $log): ?>
                                    <div class="log-entry">
                                        <span class="log-time text-muted">[<?= formatLogTime($log['time']) ?>]</span>
                                        <span class="log-level badge-<?= 
                                            strpos($log['message'], 'error') !== false ? 'danger' : 
                                            (strpos($log['message'], 'warning') !== false ? 'warning' : 'info') 
                                        ?>"><?= htmlspecialchars($log['topics'] ?? 'system') ?></span>
                                        <span class="log-message"><?= htmlspecialchars($log['message']) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Logs Card -->
                    <div class="col-md-6">
                        <div class="card card-dark">
                            <div class="card-header border-0">
                                <h3 class="card-title">
                                    <i class="fas fa-terminal"></i> Dashboard Logs
                                    <span class="badge bg-secondary float-right" id="system-count"><?= count($system_logs) ?></span>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" onclick="refreshLogs('system')" title="Refresh">🔄️
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-tool text-danger" onclick="clearSystemLogs()" title="Clear">🗑️
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0 console-container">
                                <div class="console-log" id="system-logs">
                                    <?php foreach (array_slice($system_logs, 0, 100) as $log): ?>
                                    <div class="log-entry">
                                        <span class="log-time text-muted">[<?= formatLogTime($log['timestamp']) ?>]</span>
                                        <span class="log-user text-info"><?= htmlspecialchars($log['user'] ?? 'System') ?></span>
                                        <span class="log-action text-warning"><?= htmlspecialchars($log['action']) ?></span>
                                        <span class="log-details"><?= htmlspecialchars($log['details'] ?? '') ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'footer.php'; ?>
</div>

<style>
/* Console-like styling */
.card-dark {
    background-color: #1e1e1e;
    color: #e0e0e0;
    border: 1px solid #444;
}

.card-dark .card-header {
    border-bottom: 1px solid #444;
    background-color: #2d2d2d;
}

.console-container {
    height: 600px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    background-color: #1e1e1e;
}

.console-log {
    padding: 10px;
}

.log-entry {
    margin-bottom: 3px;
    padding: 2px 5px;
    border-left: 2px solid #444;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.log-entry:hover {
    background-color: #2a2a2a;
}

.log-time {
    color: #6c757d;
    margin-right: 8px;
    min-width: 50px;
    display: inline-block;
}

.log-level {
    padding: 0 4px;
    margin-right: 8px;
    font-size: 10px;
    border-radius: 3px;
}

.log-message, .log-details {
    color: #d4d4d4;
}

.log-user {
    color: #4ec9b0;
    margin-right: 8px;
}

.log-action {
    color: #dcdcaa;
    margin-right: 8px;
}

.badge-danger { background-color: #dc3545; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-info { background-color: #17a2b8; }
.badge-success { background-color: #28a745; }

/* Custom scrollbar */
.console-container::-webkit-scrollbar {
    width: 8px;
}

.console-container::-webkit-scrollbar-track {
    background: #2d2d2d;
}

.console-container::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 4px;
}

.console-container::-webkit-scrollbar-thumb:hover {
    background: #666;
}
</style>

<script>
// Refresh logs via AJAX
function refreshLogs(type) {
    fetch(`fetch_logs.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLogDisplay(type, data.data);
                document.getElementById(`${type}-count`).textContent = data.data.length;
            }
        });
}

// Update log display
function updateLogDisplay(type, logs) {
    const container = document.getElementById(`${type}-logs`);
    container.innerHTML = '';
    
    logs.slice(0, 100).forEach(log => {
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        
        if (type === 'mikrotik') {
            entry.innerHTML = `
                <span class="log-time text-muted">[${formatTime(log.time)}]</span>
                <span class="log-level badge-${getLogLevel(log.message)}">${log.topics || 'system'}</span>
                <span class="log-message">${escapeHtml(log.message)}</span>
            `;
        } else {
            entry.innerHTML = `
                <span class="log-time text-muted">[${formatTime(log.timestamp)}]</span>
                <span class="log-user text-info">${escapeHtml(log.user || 'System')}</span>
                <span class="log-action text-warning">${escapeHtml(log.action)}</span>
                <span class="log-details">${escapeHtml(log.details || '')}</span>
            `;
        }
        
        container.appendChild(entry);
    });
}

// Clear system logs
function clearSystemLogs() {
    if (confirm('Are you sure you want to clear all system logs?')) {
        fetch('fetch_logs.php?action=clear', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('system-logs').innerHTML = '';
                document.getElementById('system-count').textContent = '0';
            }
        });
    }
}

// Helper functions
function formatTime(timestamp) {
    try {
        const date = new Date(timestamp);
        return date.toLocaleTimeString();
    } catch (e) {
        return 'Invalid';
    }
}

function getLogLevel(message) {
    return message.includes('error') ? 'danger' : 
           message.includes('warning') ? 'warning' : 'info';
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>