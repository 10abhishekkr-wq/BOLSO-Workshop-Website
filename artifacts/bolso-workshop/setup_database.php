<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/db_schema.php';

$isCli = (PHP_SAPI === 'cli');

$pdo = bolso_db();
$res = bolso_ensure_schema($pdo);

if ($isCli) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "=== BOLSO Database Setup ===\n\n";
    if (!$res['success']) {
        echo "FAILED:\n";
        foreach ($res['errors'] as $err) {
            echo " - " . $err . "\n";
        }
        exit(1);
    }
    echo "SUCCESS:\n";
    foreach ($res['created'] as $msg) {
        echo " ✓ " . $msg . "\n";
    }
    echo "\nDatabase is completely ready!\n";
    echo "Admin Login: admin / bolso2026\n";
    exit(0);
}

$pageTitle = 'Database Setup';
$activePage = '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BOLSO | Database Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: var(--cream, #f7f3ed); font-family: 'DM Sans', sans-serif; }
        .setup-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 4px;
            padding: 40px;
            max-width: 580px;
            margin: 60px auto;
            box-shadow: 0 10px 30px rgba(84, 29, 44, 0.06);
        }
        .setup-title { font-family: 'Playfair Display', serif; color: #541d2c; font-size: 32px; }
        .status-badge { display: inline-block; padding: 6px 14px; border-radius: 30px; font-weight: 600; font-size: 13px; }
        .status-success { background: #dbe8d3; color: #3b5c30; }
        .status-error { background: #fbe3e3; color: #9c2727; }
        .info-box { background: #faf7f2; border: 1px solid #ebe4d8; padding: 18px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <div class="text-center mb-4">
                <a href="index.php"><img src="assets/images/logo.png" alt="BOLSO" height="38" style="max-width: 160px; height: auto;"></a>
            </div>

            <?php if ($res['success']): ?>
                <div class="text-center mb-4">
                    <span class="status-badge status-success mb-2"><i class="bi bi-check-circle-fill me-1"></i> Database Ready</span>
                    <h1 class="setup-title mt-2">Setup Complete</h1>
                    <p class="text-muted small">All required tables and initial records have been verified.</p>
                </div>

                <div class="info-box mb-4">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-check text-success me-1"></i> System Status</h6>
                    <ul class="list-unstyled mb-0 small text-secondary">
                        <?php foreach ($res['created'] as $msg): ?>
                            <li class="mb-1"><i class="bi bi-check2 text-success me-2"></i><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="info-box mb-4" style="border-left: 4px solid #541d2c;">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-person-lock me-1"></i> Default Admin Credentials</h6>
                    <div class="small text-secondary">
                        <div><strong>Username:</strong> <code>admin</code></div>
                        <div><strong>Password:</strong> <code>bolso2026</code></div>
                        <div class="text-muted mt-1" style="font-size: 11px;">You can change this password after signing in under <em>Studio Settings</em>.</div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="admin/login.php" class="btn btn-primary-bolso py-2">Go to Admin Login <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="index.php" class="btn btn-outline-secondary py-2">Go to Homepage</a>
                </div>

            <?php else: ?>
                <div class="text-center mb-4">
                    <span class="status-badge status-error mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Setup Error</span>
                    <h1 class="setup-title mt-2">Action Required</h1>
                    <p class="text-muted small">Could not complete automatic database setup.</p>
                </div>

                <div class="alert alert-danger small">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($res['errors'] as $err): ?>
                            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <p class="small text-muted">Please check that your MySQL database service is running and that credentials in <code>config/config.php</code> or <code>config/config.local.php</code> are correct.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>