<?php
session_start();
require_once 'user_operations.php';
// At the top of your files after session start
function logAction($action, $details = '') {
    $log_file = 'system_logs.json';
    $logs = file_exists($log_file) ? json_decode(file_get_contents($log_file), true) : [];
    
    $new_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user' => $_SESSION['username'] ?? 'System',
        'action' => $action,
        'details' => $details
    ];
    
    array_push($logs, $new_log);
    file_put_contents($log_file, json_encode($logs));
}



// Initialize data with fallback values
$hotspot_users = [];
$active_users = [];
$profiles = [];

try {
    $userOps = new UserOperations();
    $hotspot_users = $userOps->getHotspotUsers() ?: [];
    $active_users = $userOps->getActiveUsers() ?: [];
    $profiles = $userOps->getProfiles() ?: [];
} catch (Exception $e) {
    $_SESSION['error'] = 'Connection error: ' . $e->getMessage();
}

// Simplified error-safe helper functions
function displayValue($value) {
    return !empty($value) ? htmlspecialchars($value) : 'N/A';
}

function formatBytesSafe($bytes) {
    return (is_numeric($bytes) && $bytes > 0) ? 
        number_format($bytes / 1024 / 1024, 2) . ' MB' : 'N/A';
}

function getUserStatus($username, $active_users) {
    if (!is_array($active_users)) return ['class' => 'bg-warning', 'text' => 'N/A'];
    
    foreach ($active_users as $user) {
        if (($user['user'] ?? '') === $username) {
            return ['class' => 'bg-success', 'text' => 'Online'];
        }
    }
    return ['class' => 'bg-secondary', 'text' => 'Offline'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>User Management</title>
    <style>
        .compact-table td, .compact-table th { padding: 0.75rem; }
        .action-buttons .btn { margin: 0 2px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <!-- Alert Messages -->
                <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); endif; ?>

                <!-- Main Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Hotspot Users</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-plus-lg"></i> Add User
                            </button>
                        </div>
                    </div>
                    
                    <!-- User Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table compact-table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Profile</th>
                                        <th>Data Limit</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($hotspot_users)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No users found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($hotspot_users as $user): ?>
                                        <tr>
                                            <td><?= displayValue($user['name'] ?? '') ?></td>
                                            <td><?= displayValue($user['profile'] ?? '') ?></td>
                                            <td><?= formatBytesSafe($user['limit-bytes-total'] ?? 0) ?></td>
                                            <td>
                                                <?php $status = getUserStatus($user['name'] ?? '', $active_users); ?>
                                                <span class="badge <?= $status['class'] ?>">
                                                    <?= $status['text'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end action-buttons">
                                                <button class="btn btn-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal" 
                                                    data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES) ?>'>
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form method="post" action="delete_user.php" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $user['.id'] ?? '' ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Permanently delete this user?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="add_user.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">New Hotspot User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="[A-Za-z0-9_]+" title="Alphanumeric characters and underscores only">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="showPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Profile *</label>
                                    <select class="form-select" name="profile" required>
                                        <?php foreach ($profiles as $profile): ?>
                                        <option value="<?= htmlspecialchars($profile['name'] ?? '') ?>">
                                            <?= htmlspecialchars($profile['name'] ?? 'N/A') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Data Limit (MB)</label>
                                    <input type="number" class="form-control" 
                                           name="limit" min="0" step="100"
                                           placeholder="Unlimited">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="modify_user.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="[A-Za-z0-9_]+" title="Alphanumeric characters and underscores only">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password">
                                <button class="btn btn-outline-secondary" type="button" id="showEditPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Profile *</label>
                                    <select class="form-select" name="profile" required>
                                        <?php foreach ($profiles as $profile): ?>
                                        <option value="<?= htmlspecialchars($profile['name'] ?? '') ?>">
                                            <?= htmlspecialchars($profile['name'] ?? 'N/A') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Data Limit (MB)</label>
                                    <input type="number" class="form-control" 
                                           name="limit" min="0" step="100"
                                           placeholder="Unlimited">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    function togglePassword(inputId, buttonId) {
        const input = document.querySelector(inputId);
        const button = document.querySelector(buttonId);
        if (input && button) {
            button.addEventListener('click', () => {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                button.querySelector('i').classList.toggle('bi-eye');
                button.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }
    }

    togglePassword('#addUserModal [name="password"]', '#showPassword');
    togglePassword('#editUserModal [name="password"]', '#showEditPassword');

    // Edit modal handling
    const editModal = document.getElementById('editUserModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            try {
                const user = JSON.parse(button.dataset.user);
                this.querySelector('#editUserId').value = user['.id'] || '';
                this.querySelector('[name="username"]').value = user.name || 'N/A';
                this.querySelector('[name="profile"]').value = user.profile || 'default';
                this.querySelector('[name="limit"]').value = user['limit-bytes-total'] 
                    ? Math.round(user['limit-bytes-total'] / 1024 / 1024)
                    : '';
            } catch(error) {
                console.error('Error loading user data:', error);
                this.querySelector('.modal-body').innerHTML = `
                    <div class="alert alert-danger">
                        Error loading user data: ${error.message}
                    </div>
                `;
            }
        });
    }
});
</script>
</body>
</html>