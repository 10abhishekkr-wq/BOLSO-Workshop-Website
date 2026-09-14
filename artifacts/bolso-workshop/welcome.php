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
        <a href="index.php" class="btn btn-primary-bolso me-2 shadow-sm" style="padding: 10px 24px; font-size: 14px;">
            <i class="bi bi-house-door me-1"></i> Enter Website
        </a>
        <a href="workshops.php" class="btn btn-light-bolso shadow-sm" style="padding: 10px 24px; font-size: 14px;">
            <i class="bi bi-palette me-1"></i> Explore Workshops
        </a>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
