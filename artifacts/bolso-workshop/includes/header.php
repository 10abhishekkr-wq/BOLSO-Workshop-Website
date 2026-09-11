<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

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
    <link rel="icon" type="image/png" href="<?= $isAdminArea ? '../assets/images/favicon.png' : 'assets/images/favicon.png' ?>">
    <link rel="shortcut icon" href="<?= $isAdminArea ? '../favicon.ico' : 'favicon.ico' ?>">
    <link rel="apple-touch-icon" href="<?= $isAdminArea ? '../assets/images/favicon.png' : 'assets/images/favicon.png' ?>">
    <link rel="stylesheet" href="<?= ($isAdminArea ? '../assets/css/style.css?v=' : 'assets/css/style.css?v=') . (file_exists(__DIR__ . '/../assets/css/style.css') ? (string)filemtime(__DIR__ . '/../assets/css/style.css') : '2.2') ?>">
</head>
<body class="<?= $isAdminArea ? 'admin-body' : '' ?>">
<div class="site-grain" aria-hidden="true"></div>
<?php if ($isAdminArea): ?>
    <?php if (!empty($_SESSION['admin_id'])): ?>
        <?php require __DIR__ . '/admin_nav.php'; ?>
    <?php else: ?>
        <nav class="admin-topbar">
            <a class="brand-lockup" href="../index.php" aria-label="BOLSO home">
                <img src="../assets/images/logo.png" alt="BOLSO" class="brand-logo-img" width="120" height="30" style="height: 30px; width: auto; max-width: 125px; display: inline-block; vertical-align: middle;">
                <span class="brand-tagline admin-tagline">Admin studio</span>
            </a>
        </nav>
    <?php endif; ?>
<?php else: ?>
    <nav class="navbar navbar-expand-lg bolso-nav">
        <div class="container">
            <a class="brand-lockup" href="index.php" aria-label="BOLSO home">
                <img src="assets/images/logo.png" alt="BOLSO" class="brand-logo-img" width="130" height="34" style="height: 34px; width: auto; max-width: 140px; display: inline-block; vertical-align: middle;">
                <span class="brand-tagline">Art · Emotion · Fashion</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 gap-xl-3">
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-house-door nav-icon"></i> <span>Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'workshops' ? 'active' : '' ?>" href="workshops.php">
                            <i class="bi bi-palette nav-icon"></i> <span>Workshops</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'registration' ? 'active' : '' ?>" href="registration.php">
                            <i class="bi bi-journal-bookmark nav-icon"></i> <span>Registration</span>
                        </a>
                    </li>
                    
                    <?php if (is_user_logged_in()): ?>
                        <?php
                            $navUser = current_user();
                            $navFirstName = explode(' ', trim($navUser['name'] ?? 'Student'))[0];
                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle nav-user-btn <?= $activePage === 'my-workshops' ? 'active' : '' ?>" href="#" id="userNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle nav-icon text-success"></i> <span>Hi, <?= htmlspecialchars($navFirstName, ENT_QUOTES, 'UTF-8') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end bolso-dropdown" aria-labelledby="userNavDropdown">
                                <li><a class="dropdown-item <?= $activePage === 'my-workshops' ? 'active' : '' ?>" href="my-workshops.php"><i class="bi bi-grid me-2"></i> My Workshops</a></li>
                                <li><a class="dropdown-item" href="registration.php"><i class="bi bi-plus-circle me-2"></i> Book Workshop</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'login' ? 'active' : '' ?>" href="login.php">
                                <i class="bi bi-person-circle nav-icon"></i> <span>Sign In</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link admin-nav-link" href="admin/" title="Admin Studio">
                            <i class="bi bi-shield-lock nav-icon"></i> <span>Admin</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-cta" href="registration.php">
                            <span>Reserve your spot</span>
                            <span class="nav-cta-arrow"><i class="bi bi-arrow-up-right"></i></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php endif; ?>