<?php
declare(strict_types=1);

$pageTitle = 'Welcome to BOLSO Fabric Art Studio';
$activePage = 'welcome';
$isStandalone = true;
require __DIR__ . '/includes/header.php';
?>

<main class="welcome-page-main">
    <?php require __DIR__ . '/includes/splash_screen.php'; ?>

    <!-- Secondary Fallback Content for direct exploration if Javascript is disabled or user scrolls -->
    <div class="welcome-standalone-footer-nav container text-center py-4" style="position: fixed; bottom: 15px; left: 0; right: 0; z-index: 100000; pointer-events: auto;">
        <a href="index.php?enter=1" class="btn btn-primary-bolso me-2 shadow-sm" style="padding: 10px 24px; font-size: 14px;">
            <i class="bi bi-house-door me-1"></i> Enter Website
        </a>
        <a href="workshops.php" class="btn btn-light-bolso shadow-sm me-2" style="padding: 10px 24px; font-size: 14px;">
            <i class="bi bi-palette me-1"></i> Explore Workshops
        </a>
        <a href="admin/index.php" class="btn btn-outline-light shadow-sm" style="padding: 10px 20px; font-size: 13px; border-radius: 999px; background: rgba(0,0,0,0.3); backdrop-filter: blur(6px); border-color: rgba(255,255,255,0.35);">
            <i class="bi bi-shield-lock me-1 text-warning"></i> Admin Panel
        </a>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
