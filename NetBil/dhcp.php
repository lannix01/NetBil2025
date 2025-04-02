<?php
require('routeros_api.class.php');
$API = new RouterosAPI();
$API->connect('192.168.1.101', 'lannix', 'lannix123NIC');

// Fetch data for all tables
$dhcpClients = $API->comm('/ip/dhcp-client/print');
$dhcpServers = $API->comm('/ip/dhcp-server/print');
$dhcpLeases = $API->comm('/ip/dhcp-server/lease/print');
$pools = $API->comm('/ip/pool/print');

$API->disconnect();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>DHCP Management - BrenNet</title>
    <style>
        .add-new-btn { margin-bottom: 15px; }
        .modal-footer { justify-content: space-between; }
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
                        <h1>DHCP Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back</a></li>
                            <li class="breadcrumb-item active">DHCP</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- ISP/Link Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ISP/Link (DHCP Clients)</h3>
                        <button type="button" class="btn btn-primary float-right add-new-btn" data-toggle="modal" data-target="#addDhcpClientModal">
                            <i class="fas fa-plus"></i> Add New
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Interface</th>
                                    <th>Status</th>
                                    <th>Address</th>
                                    <th>Gateway</th>
                                    <th>DNS</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dhcpClients as $client): ?>
                                <tr>
                                    <td><?= htmlspecialchars($client['interface']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $client['status'] == 'bound' ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars($client['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($client['address'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($client['gateway'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($client['dhcp-server'] ?? 'N/A') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info">✏️</button>
                                        <button class="btn btn-sm btn-danger">🚫</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Net Output Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Net Output (DHCP Servers & Leases)</h3>
                        <div class="float-right">
                            <button type="button" href="api/dhcp_server_add.php" class="btn btn-primary add-new-btn" data-toggle="modal" data-target="#addDhcpServerModal">
                                <i class="fas fa-plus"></i> Add Server
                            </button>
                            <button type="button" href="api/dhcp_lease_add.php" class="btn btn-success add-new-btn" data-toggle="modal" data-target="#addDhcpLeaseModal">
                                <i class="fas fa-plus"></i> Add Lease
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>DHCP Servers</h4>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Interface</th>
                                            <th>Address Pool</th>
                                            <th>Lease Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dhcpServers as $server): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($server['name']) ?></td>
                                            <td><?= htmlspecialchars($server['interface']) ?></td>
                                            <td><?= htmlspecialchars($server['address-pool']) ?></td>
                                            <td><?= htmlspecialchars($server['lease-time']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info">✏️</button>
                                                <button class="btn btn-sm btn-danger">🚫</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>DHCP Leases</h4>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>IP Address</th>
                                            <th>MAC Address</th>
                                            <th>Hostname</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dhcpLeases as $lease): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($lease['address']) ?></td>
                                            <td><?= htmlspecialchars($lease['mac-address']) ?></td>
                                            <td><?= htmlspecialchars($lease['host-name'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $lease['status'] == 'bound' ? 'success' : 'warning' ?>">
                                                    <?= htmlspecialchars($lease['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info">✏️</button>
                                                <button class="btn btn-sm btn-danger">🚫</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pools Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">DHCP Pools</h3>
                        <button type="button" class="btn btn-primary float-right add-new-btn" data-toggle="modal" data-target="#addPoolModal">
                            <i class="fas fa-plus"></i> Add New
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Ranges</th>
                                    <th>Next Pool</th>
                                    <th>Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pools as $pool): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pool['name']) ?></td>
                                    <td><?= htmlspecialchars($pool['ranges']) ?></td>
                                    <td><?= htmlspecialchars($pool['next-pool'] ?? 'N/A') ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?= rand(10, 90) ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info">✏️</button>
                                        <button class="btn btn-sm btn-danger">🚫</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'footer.php'; ?>
</div>

<!-- Add DHCP Client Modal -->
<div class="modal fade" id="addDhcpClientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add DHCP Client</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="dhcpClientForm">
                    <div class="form-group">
                        <label>Interface</label>
                        <input type="text" class="form-control" name="interface" required>
                    </div>
                    <div class="form-group">
                        <label>Add Default Route</label>
                        <select class="form-control" name="add-default-route">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Use Peer DNS</label>
                        <select class="form-control" name="use-peer-dns">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Disabled</label>
                        <select class="form-control" name="disabled">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addDhcpClient()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add DHCP Server Modal -->
<div class="modal fade" id="addDhcpServerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add DHCP Server</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="dhcpServerForm">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Interface</label>
                        <input type="text" class="form-control" name="interface" required>
                    </div>
                    <div class="form-group">
                        <label>Address Pool</label>
                        <input type="text" class="form-control" name="address-pool" required>
                    </div>
                    <div class="form-group">
                        <label>Lease Time</label>
                        <input type="text" class="form-control" name="lease-time" value="10m" required>
                    </div>
                    <div class="form-group">
                        <label>Disabled</label>
                        <select class="form-control" name="disabled">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addDhcpServer()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add DHCP Lease Modal -->
<div class="modal fade" id="addDhcpLeaseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add DHCP Lease</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="dhcpLeaseForm">
                    <div class="form-group">
                        <label>MAC Address</label>
                        <input type="text" class="form-control" name="mac-address" required>
                    </div>
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    <div class="form-group">
                        <label>Hostname</label>
                        <input type="text" class="form-control" name="host-name">
                    </div>
                    <div class="form-group">
                        <label>Server</label>
                        <input type="text" class="form-control" name="server" required>
                    </div>
                    <div class="form-group">
                        <label>Disabled</label>
                        <select class="form-control" name="disabled">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addDhcpLease()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Pool Modal -->
<div class="modal fade" id="addPoolModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add IP Pool</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="poolForm">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Ranges (e.g., 192.168.1.100-192.168.1.200)</label>
                        <input type="text" class="form-control" name="ranges" required>
                    </div>
                    <div class="form-group">
                        <label>Next Pool</label>
                        <input type="text" class="form-control" name="next-pool">
                    </div>
                    <div class="form-group">
                        <label>Disabled</label>
                        <select class="form-control" name="disabled">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addPool()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Function to add DHCP Client
function addDhcpClient() {
    const formData = $('#dhcpClientForm').serialize();
    $.post('api/dhcp_client_add.php', formData, function(response) {
        if(response.success) {
            $('#addDhcpClientModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    }).fail(function() {
        alert('Error submitting form');
    });
}

// Function to add DHCP Server
function addDhcpServer() {
    const formData = $('#dhcpServerForm').serialize();
    $.post('api/dhcp_server_add.php', formData, function(response) {
        if(response.success) {
            $('#addDhcpServerModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    }).fail(function() {
        alert('Error submitting form');
    });
}

// Function to add DHCP Lease
function addDhcpLease() {
    const formData = $('#dhcpLeaseForm').serialize();
    $.post('api/dhcp_lease_add.php', formData, function(response) {
        if(response.success) {
            $('#addDhcpLeaseModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    }).fail(function() {
        alert('Error submitting form');
    });
}

// Function to add IP Pool
function addPool() {
    const formData = $('#poolForm').serialize();
    $.post('api/pool_add.php', formData, function(response) {
        if(response.success) {
            $('#addPoolModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    }).fail(function() {
        alert('Error submitting form');
    });
}
<!-- ✏️ DHCP Server Modal -->
<div class="modal fade" id="✏️DhcpServerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit DHCP Server</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editDhcpServerForm">
                    <input type="hidden" name="id" id="editServerId">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" id="editServerName" required>
                    </div>
                    <div class="form-group">
                        <label>Interface</label>
                        <input type="text" class="form-control" name="interface" id="editServerInterface" required>
                    </div>
                    <div class="form-group">
                        <label>Address Pool</label>
                        <input type="text" class="form-control" name="address-pool" id="editServerPool" required>
                    </div>
                    <div class="form-group">
                        <label>Lease Time</label>
                        <input type="text" class="form-control" name="lease-time" id="editServerLeaseTime" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="disabled" id="editServerStatus">
                            <option value="no">Enabled</option>
                            <option value="yes">Disabled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateDhcpServer()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item?</p>
                <input type="hidden" id="deleteItemId">
                <input type="hidden" id="deleteItemType">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">🚫</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentEditId = null;

// Function to open edit modal
function openEditModal(type, id) {
    currentEditId = id;
    
    // Fetch item data
    $.post(`api/dhcp_${type}_get.php`, { 
        id: id,
        csrf_token: '<?= generateCsrfToken() ?>' 
    }, function(response) {
        if (response.success) {
            const data = response.data;
            
            if (type === 'server') {
                $('#editServerId').val(data['.id']);
                $('#editServerName').val(data.name);
                $('#editServerInterface').val(data.interface);
                $('#editServerPool').val(data['address-pool']);
                $('#editServerLeaseTime').val(data['lease-time']);
                $('#editServerStatus').val(data.disabled || 'no');
                $('#editDhcpServerModal').modal('show');
            }
            // Add other types (client, lease, pool) similarly
            
        } else {
            showError(response.message);
        }
    }).fail(handleAjaxError);
}

// Function to update item
function updateDhcpServer() {
    const formData = $('#editDhcpServerForm').serialize();
    
    $.post('api/dhcp_server_edit.php', formData, function(response) {
        if (response.success) {
            $('#editDhcpServerModal').modal('hide');
            showSuccess('DHCP Server updated successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showError(response.message);
        }
    }).fail(handleAjaxError);
}

// Function to show delete confirmation
function showDeleteConfirmation(type, id) {
    $('#deleteItemId').val(id);
    $('#deleteItemType').val(type);
    $('#deleteConfirmationModal').modal('show');
}

// Function to confirm deletion
function confirmDelete() {
    const id = $('#deleteItemId').val();
    const type = $('#deleteItemType').val();
    
    $.post(`api/dhcp_${type}_delete.php`, { 
        id: id,
        csrf_token: '<?= generateCsrfToken() ?>' 
    }, function(response) {
        if (response.success) {
            $('#deleteConfirmationModal').modal('hide');
            showSuccess('Item deleted successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showError(response.message);
        }
    }).fail(handleAjaxError);
}

// Helper functions
function showError(message) {
    Toast.fire({
        icon: 'error',
        title: message
    });
}

function showSuccess(message) {
    Toast.fire({
        icon: 'success',
        title: message
    });
}

function handleAjaxError(xhr) {
    let message = 'An error occurred';
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    }
    showError(message);
}

// Initialize Toast notifications
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

</script>

</body>
</html>