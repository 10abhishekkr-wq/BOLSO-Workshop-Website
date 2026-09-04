<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = bolso_db();
$error = null;
$adminUser = null;

if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT id, username, created_at FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $adminUser = $stmt->fetch();

        // Handle Change Password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_admin_csrf($_POST['csrf_token'] ?? null)) {
                $error = 'Security session expired. Please refresh.';
            } else {
                $action = (string)($_POST['action'] ?? '');
                if ($action === 'change_password') {
                    $currentPassword = (string)($_POST['current_password'] ?? '');
                    $newPassword = (string)($_POST['new_password'] ?? '');
                    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

                    if (strlen($newPassword) < 6) {
                        $error = 'New password must be at least 6 characters long.';
                    } elseif ($newPassword !== $confirmPassword) {
                        $error = 'New password and confirmation do not match.';
                    } else {
                        $pStmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = :id LIMIT 1');
                        $pStmt->execute([':id' => $_SESSION['admin_id']]);
                        $authRecord = $pStmt->fetch();

                        if ($authRecord && password_verify($currentPassword, $authRecord['password_hash'])) {
                            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                            $uStmt = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
                            $uStmt->execute([':hash' => $newHash, ':id' => $_SESSION['admin_id']]);
                            admin_flash('success', 'Your admin password has been successfully updated.');
                            header('Location: settings.php');
                            exit;
                        } else {
                            $error = 'Your current password is incorrect.';
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Settings page error: ' . $e->getMessage());
        $error = 'Settings could not be loaded.';
    }
}

// System diagnostics
$dbHost = bolso_config('db_host');
$dbName = bolso_config('db_name');
$rzpKey = bolso_config('payment_key');
$maskedKey = ($rzpKey && $rzpKey !== 'YOUR_PAYMENT_KEY') 
    ? substr($rzpKey, 0, 8) . '...' . substr($rzpKey, -4) 
    : 'Not configured';
$whatsappNum = bolso_config('whatsapp');

$pageTitle = 'Studio Settings';
$isAdminArea = true;
$adminActiveTab = 'settings';
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-page-container">
    <div class="container-fluid px-lg-4">
        <div class="dashboard-heading">
            <div>
                <span class="eyebrow">BOLSO Studio / Administration</span>
                <h1>Security & System Settings</h1>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert bolso-alert mt-4" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php $flash = take_admin_flash(); if ($flash): ?>
            <div class="alert alert-success bolso-success-alert mt-4" role="alert">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="row g-4 mt-2">
            <!-- Change Password Card -->
            <div class="col-lg-6">
                <div class="admin-card h-100">
                    <span class="eyebrow">Authentication</span>
                    <h3 class="admin-card-title mb-3">Change Admin Password</h3>
                    <p class="text-muted small">Update your admin studio password. Use a strong password to ensure security.</p>

                    <form method="post" class="admin-form mt-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password (min 6 characters) *</label>
                            <input id="new_password" type="password" name="new_password" class="form-control" required minlength="6" autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input id="confirm_password" type="password" name="confirm_password" class="form-control" required minlength="6" autocomplete="new-password">
                        </div>

                        <button class="btn btn-primary-bolso mt-2" type="submit">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- System Diagnostics & Environment -->
            <div class="col-lg-6">
                <div class="admin-card h-100">
                    <span class="eyebrow">Health & Environment</span>
                    <h3 class="admin-card-title mb-3">System Diagnostics</h3>
                    <p class="text-muted small">Configuration overview and operational connectivity status.</p>

                    <div class="list-group list-group-flush mt-3">
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Logged in Account</strong>
                                <small class="d-block text-muted">Primary administrator</small>
                            </div>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-person me-1"></i> <?= htmlspecialchars($adminUser['username'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>MySQL Database</strong>
                                <small class="d-block text-muted"><?= htmlspecialchars($dbHost) ?> &bull; db: <?= htmlspecialchars($dbName) ?></small>
                            </div>
                            <?php if ($pdo): ?>
                                <span class="badge-active"><i class="bi bi-check-circle"></i> Connected</span>
                            <?php else: ?>
                                <span class="badge-inactive"><i class="bi bi-x-circle"></i> Disconnected</span>
                            <?php endif; ?>
                        </div>

                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Razorpay Integration</strong>
                                <small class="d-block text-muted">Key: <?= htmlspecialchars($maskedKey) ?></small>
                            </div>
                            <?php if ($rzpKey && $rzpKey !== 'YOUR_PAYMENT_KEY'): ?>
                                <span class="badge-active"><i class="bi bi-check-circle"></i> Configured</span>
                            <?php else: ?>
                                <span class="badge-inactive"><i class="bi bi-exclamation-triangle"></i> Needs Setup</span>
                            <?php endif; ?>
                        </div>

                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>WhatsApp Support</strong>
                                <small class="d-block text-muted">Studio contact line</small>
                            </div>
                            <span class="badge bg-light text-dark border">
                                +<?= htmlspecialchars($whatsappNum) ?>
                            </span>
                        </div>

                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>PHP Runtime</strong>
                                <small class="d-block text-muted"><?= htmlspecialchars(php_uname('s')) ?> &bull; <?= htmlspecialchars(PHP_VERSION) ?></small>
                            </div>
                            <span class="badge bg-light text-dark border">PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?></span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light border rounded">
                        <small class="text-muted d-block" style="font-size: 11px;">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            Environment variables and credentials can be updated safely in <code>config/config.local.php</code>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
