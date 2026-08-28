<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$pageTitle = $pageTitle ?? 'Learn to turn fabric into art';
$activePage = $activePage ?? '';
$isAdminArea = $isAdminArea ?? false;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BOLSO teaches beautiful, soft and wearable fabric painting in small-batch online and offline workshops.">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle . ' | BOLSO', ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="Art, Emotion, Fashion. Learn to turn fabric into art with BOLSO.">
    <meta property="og:type" content="website">
    <title><?= htmlspecialchars($pageTitle . ' | BOLSO', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $isAdminArea ? '../assets/css/style.css' : 'assets/css/style.css' ?>">
</head>
<body class="<?= $isAdminArea ? 'admin-body' : '' ?>">
<div class="site-grain" aria-hidden="true"></div>
<?php if ($isAdminArea): ?>
    <nav class="admin-topbar">
        <a class="brand-lockup" href="../index.php">
            <span class="brand-mark">B</span>
            <span><strong>BOLSO</strong><small>Admin studio</small></span>
        </a>
        <?php if (!empty($_SESSION['admin_id'])): ?>
            <a class="admin-logout" href="logout.php">Log out <i class="bi bi-arrow-right"></i></a>
        <?php endif; ?>
    </nav>
<?php else: ?>
    <nav class="navbar navbar-expand-lg bolso-nav">
        <div class="container">
            <a class="brand-lockup" href="index.php" aria-label="BOLSO home">
                <span class="brand-mark">B</span>
                <span><strong>BOLSO</strong><small>Art, Emotion, Fashion.</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4">
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'workshops' ? 'active' : '' ?>" href="workshops.php">Workshops</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'registration' ? 'active' : '' ?>" href="registration.php">Registration</a></li>
                    <li class="nav-item"><a class="nav-cta" href="registration.php">Reserve your spot <i class="bi bi-arrow-up-right"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>