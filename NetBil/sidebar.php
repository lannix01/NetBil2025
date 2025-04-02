<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
        <img src="logo.png" alt="NetBil Logo" class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light">NetBil</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Users -->
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-people"></i>
                        <p>Users</p>
                    </a>
                </li>

                <!-- Devices -->
                <li class="nav-item">
                    <a href="devices.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-laptop"></i>
                        <p>Devices/Activity</p>
                    </a>
                </li>

                <!-- IP/DHCP Pools -->
                <li class="nav-item">
                    <a href="dhcp.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dhcp.php' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-hdd-stack"></i>
                        <p>IP/DHCP Pools</p>
                    </a>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a href="reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-graph-up"></i>
                        <p>Reports</p>
                    </a>
                </li>

                <!-- Logs -->
                <li class="nav-item">
                    <a href="logs.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-journal-text"></i>
                        <p>Logs</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
