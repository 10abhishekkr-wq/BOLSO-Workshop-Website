<?php
declare(strict_types=1);

$currentAdminPage = $adminActiveTab ?? basename($_SERVER['PHP_SELF'], '.php');
?>
<header class="admin-studio-header">
    <div class="admin-topbar-wrapper">
        <div class="container-fluid px-lg-4">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center gap-3">
                    <a class="brand-lockup admin-brand" href="dashboard.php" aria-label="BOLSO Admin Studio">
                        <img src="../assets/images/logo.png" alt="BOLSO" class="brand-logo-img admin-nav-logo" width="115" height="28" style="height: 28px; width: auto; max-width: 120px; display: inline-block; vertical-align: middle;">
                        <span class="brand-tagline admin-tagline">Admin Studio</span>
                    </a>
                    <span class="badge bg-dark-subtle text-dark-emphasis px-2 py-1 rounded-pill d-none d-md-inline-flex align-items-center gap-1">
                        <i class="bi bi-shield-check text-success"></i> Studio Manager
                    </span>
                </div>
                
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <a class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex align-items-center gap-1" href="../index.php" target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Live Site
                    </a>
                    <div class="admin-user-pill d-none d-md-flex align-items-center gap-2">
                        <i class="bi bi-person-circle"></i>
                        <span><?= htmlspecialchars((string)($_SESSION['admin_username'] ?? 'Admin'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <a class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Log out
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Admin Navigation Bar -->
    <nav class="admin-subnav-bar">
        <div class="container-fluid px-lg-4">
            <ul class="admin-nav-tabs">
                <li class="admin-nav-item">
                    <a class="admin-tab-link <?= $currentAdminPage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="bi bi-grid-1x2"></i> Overview
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a class="admin-tab-link <?= $currentAdminPage === 'registrations' ? 'active' : '' ?>" href="registrations.php">
                        <i class="bi bi-people"></i> Registrations
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a class="admin-tab-link <?= $currentAdminPage === 'workshops' ? 'active' : '' ?>" href="workshops.php">
                        <i class="bi bi-palette"></i> Workshops
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a class="admin-tab-link <?= $currentAdminPage === 'payments' ? 'active' : '' ?>" href="payments.php">
                        <i class="bi bi-credit-card"></i> Payments
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a class="admin-tab-link <?= $currentAdminPage === 'notifications' ? 'active' : '' ?>" href="notifications.php">
                        <i class="bi bi-bell"></i> Notifications
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a class="admin-tab-link <?= $currentAdminPage === 'settings' ? 'active' : '' ?>" href="settings.php">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>
