<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
