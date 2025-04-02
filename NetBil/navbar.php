<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <!-- Left Section -->
        <div class="d-flex align-items-center">
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand d-none d-sm-block" href="dashboard.php">Admin Portal</a>
        </div>

        <!-- Right Section -->
        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <a class="btn btn-link text-white position-relative" 
                   role="button" 
                   data-bs-toggle="dropdown" 
                   aria-expanded="false"
                   id="notificationBell">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        log
                        <span class="visually-hidden"></span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationBell">
                    <div class="card" style="width: 320px; max-height: 60vh;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0">Update System</h6>
                            <button class="btn btn-sm btn-outline-secondary">Done</button>
                        </div>
                        <div class="card-body p-0">
                            <div id="notificationContent" class="list-group list-group-flush" style="overflow-y: auto;">
                                <!-- Loading State -->
                                <div class="list-group-item text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">updating...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-muted small">
                            Last updated: <span class="update-time">Just now</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <!-- User Menu -->
<div class="dropdown">
    <a class="btn btn-link text-white d-flex align-items-center text-decoration-none dropdown-toggle"
       href="#" 
       role="button" 
       id="adminDropdown"
       data-bs-toggle="dropdown" 
       aria-expanded="false">
        <img src="profile.jpg" 
             class="profile-image rounded-circle border border-2 border-white me-2" 
             width="36" 
             height="36" 
             alt="Admin profile">
        <span class="d-none d-lg-inline">Admin</span>
        <i class="bi bi-chevron-down ms-1"></i>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow">
        <li><a class="dropdown-item" href="#">
            <i class="bi bi-house-door me-2"></i>Dashboard
        </a></li>
        <li><a class="dropdown-item" href="#">
            <i class="bi bi-gear me-2"></i>Settings
        </a></li>
        <li><a class="dropdown-item" href="#">
            <i class="bi bi-person-circle me-2"></i>Profile
        </a></li>
        <li><a class="dropdown-item" href="#">
            <i class="bi bi-shield-lock me-2"></i>Security
        </a></li>
        <li><a class="dropdown-item text-danger" href="#" id="logoutButton">
    <i class="bi bi-box-arrow-right me-2"></i>Logout
</a></li>
</ul>
</div>
        </div>
    </div>
</nav>

<script>
$(document).ready(function() {
    $('#logoutButton').click(function(e) {
        e.preventDefault();
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "logout.php";
        }
    });
});
</script>
    </ul>
</div>
        </div>
    </div>
</nav>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Admin Dropdown - Immediately show on click
    const adminDropdown = new bootstrap.Dropdown(
        document.querySelector('#adminDropdown')
    );

    // Notification System
    $('#notificationBell').click(function(e) {
        e.preventDefault();
        
        // Show loading state
        $('#notificationContent').html(`
            <div class="list-group-item text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <small class="text-muted mt-2">Checking network status...</small>
            </div>
        `);

        // First ping through MikroTik
        $.ajax({
            url: 'ping.php',
            type: 'GET',
            dataType: 'json',
            success: function(pingData) {
                if(pingData.success) {
                    // Ping succeeded - load logs
                    $.get('fetch_logs.php')
                    .done(function(logs) {
                        $('#notificationContent').html(logs);
                        $('.update-time').text(new Date().toLocaleTimeString());
                    })
                    .fail(function() {
                        $('#notificationContent').html(`
                            <div class="text-center text-danger p-3">
                                <i class="bi bi-x-circle"></i> Failed to load logs
                            </div>
                        `);
                    });
                } else {
                    // Ping failed
                    $('#notificationContent').html(`
                        <div class="text-center text-danger p-3">
                            <i class="bi bi-x-circle"></i> Network unreachable (${pingData.received}/4 packets)
                        </div>
                    `);
                }
            },
            error: function() {
                $('#notificationContent').html(`
                    <div class="text-center text-danger p-3">
                        <i class="bi bi-x-circle"></i> Ping check failed
                    </div>
                `);
            }
        });
    });

    // Profile image fallback
    $('.profile-image').on('error', function() {
        $(this).prop('src', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjggMTI4Ij48Y2lyY2xlIGN4PSI2NCIgY3k9IjQwIiByPSIyNCIgZmlsbD0iI2ZmZiIvPjxjaXJjbGUgY3g9IjY0IiBjeT0iODgiIHI9IjQ4IiBmaWxsPSIjZmZmIi8+PC9zdmc+')
            .addClass('bg-primary');
    });
});
</script>

<style>
.navbar {
    padding: 0.5rem 1rem;
    min-height: 64px;
}

.dropdown-menu {
    margin-top: 0.5rem;
    border: 1px solid rgba(0,0,0,0.1);
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.notification-item {
    transition: background-color 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.card-header {
    background-color: rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

@media (max-width: 768px) {
    .navbar-brand {
        display: none;
    }
    
    .dropdown-menu {
        position: fixed !important;
        left: 50% !important;
        transform: translateX(-50%);
        width: 90vw;
    }
}
</style>