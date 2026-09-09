<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/db_schema.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$resetRecord = null;
$tokenError = '';

if ($token === '') {
    $tokenError = 'No reset token provided. Please request a new recovery link.';
} else {
    $resetRecord = bolso_verify_reset_token('admin', $token);
    if (!$resetRecord) {
        $tokenError = 'This admin recovery link is invalid or has expired. Please request a new recovery link.';
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetRecord) {
    if (!verify_admin_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'New password and confirmation do not match.';
        } else {
            $success = bolso_complete_password_reset(
                (int)$resetRecord['id'],
                'admin',
                (int)$resetRecord['user_id'],
                $password
            );

            if ($success) {
                admin_flash('success', 'Admin password successfully updated. Please sign in with your new password.');
                header('Location: login.php');
                exit;
            } else {
                $error = 'Failed to update administrator password. Please try again.';
            }
        }
    }
}

$pageTitle = 'Set Admin Password';
$isAdminArea = true;
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-login-page">
    <div class="admin-login-card">
        <div class="admin-login-logo-wrap text-center mb-4">
            <a href="../index.php" title="Back to site">
                <img src="../assets/images/logo.png" alt="BOLSO" class="admin-login-logo" width="160" height="42" style="height: 42px; width: auto; max-width: 175px; display: inline-block; vertical-align: middle;">
            </a>
        </div>
        <span class="eyebrow">Admin security</span>
        <h1>Set new<br><em>password.</em></h1>
        <p>Choose a secure new password for your admin account.</p>

        <?php if ($tokenError !== ''): ?>
            <div class="alert alert-danger py-3 px-3 mb-4 rounded-0" role="alert" style="font-size: 13px; border-left: 3px solid var(--maroon);">
                <div class="fw-semibold mb-1"><i class="bi bi-shield-x me-1"></i> Invalid Recovery Link</div>
                <div><?= htmlspecialchars($tokenError, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <a href="forgot-password.php" class="btn btn-primary-bolso w-100 py-2">Request New Link</a>
        <?php else: ?>
            <?php if ($error !== ''): ?>
                <div class="alert bolso-alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" class="admin-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <label for="password">New Password (min 6 chars)</label>
                <div style="position: relative; width: 100%;">
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        autocomplete="new-password" 
                        required 
                        minlength="6"
                        style="padding-right: 44px; width: 100%;"
                        autofocus
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="togglePasswordVisibility('password', this)" 
                        aria-label="Toggle password visibility" 
                        title="Show password" 
                        tabindex="-1" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--ink-soft); cursor: pointer; font-size: 18px; padding: 4px; display: flex; align-items: center; justify-content: center; z-index: 3;"
                    >
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <label for="password_confirm">Confirm New Password</label>
                <div style="position: relative; width: 100%;">
                    <input 
                        id="password_confirm" 
                        type="password" 
                        name="password_confirm" 
                        autocomplete="new-password" 
                        required 
                        minlength="6"
                        style="padding-right: 44px; width: 100%;"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="togglePasswordVisibility('password_confirm', this)" 
                        aria-label="Toggle password visibility" 
                        title="Show password" 
                        tabindex="-1" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--ink-soft); cursor: pointer; font-size: 18px; padding: 4px; display: flex; align-items: center; justify-content: center; z-index: 3;"
                    >
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button class="btn btn-primary-bolso w-100 mt-3" type="submit">
                    Update Admin Password <i class="bi bi-check2 ms-1"></i>
                </button>
            </form>
        <?php endif; ?>

        <a class="text-link dark-link mt-4 d-inline-block" href="login.php">&larr; Back to admin login</a>
    </div>
</main>

<script>
function togglePasswordVisibility(fieldId, btn) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    var icon = btn ? btn.querySelector('i') : null;
    if (field.type === 'password') {
        field.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
        if (btn) {
            btn.setAttribute('title', 'Hide password');
            btn.setAttribute('aria-label', 'Hide password');
        }
    } else {
        field.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
        if (btn) {
            btn.setAttribute('title', 'Show password');
            btn.setAttribute('aria-label', 'Show password');
        }
    }
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
