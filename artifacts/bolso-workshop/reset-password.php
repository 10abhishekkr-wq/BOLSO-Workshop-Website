<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notifications.php';
require_once __DIR__ . '/includes/db_schema.php';

if (is_user_logged_in()) {
    header('Location: my-workshops.php');
    exit;
}

$token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? '')));
$resetRecord = null;
$tokenError = '';

if ($token === '') {
    $tokenError = 'No reset token provided. Please request a new password reset link.';
} else {
    $resetRecord = bolso_verify_reset_token('user', $token);
    if (!$resetRecord) {
        $tokenError = 'This password reset link is invalid or has expired. Please request a new reset link.';
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resetRecord) {
    if (!verify_user_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your security session expired. Please refresh and try again.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match. Please re-enter your password.';
        } else {
            $success = bolso_complete_password_reset(
                (int)$resetRecord['id'],
                'user',
                (int)$resetRecord['user_id'],
                $password
            );

            if ($success) {
                user_flash('success', 'Your password has been successfully updated. Please sign in below.');
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Failed to update your password. Please try again or request a new reset link.';
            }
        }
    }
}

$pageTitle = 'Reset Password';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-section section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <span class="auth-eyebrow">Set New Password</span>
                        <h1 class="auth-title">Create New <em>Password</em></h1>
                        <p class="auth-subtitle">Choose a secure password for your BOLSO student account.</p>
                    </div>

                    <?php if ($tokenError !== ''): ?>
                        <div class="alert alert-danger py-3 px-3 mb-4 rounded-0" role="alert" style="font-size: 13.5px; border-left: 3px solid var(--maroon);">
                            <div class="fw-semibold mb-1"><i class="bi bi-shield-x me-1"></i> Invalid or Expired Link</div>
                            <div><?= htmlspecialchars($tokenError, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="forgot-password.php" class="btn btn-primary-bolso py-2 px-4">Request New Reset Link</a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger py-2 px-3 mb-4 rounded-0" role="alert" style="font-size: 13px; border-left: 3px solid var(--maroon);">
                                <?php foreach ($errors as $err): ?>
                                    <div><i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="reset-password.php" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(user_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="password-field-wrap position-relative">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-control bolso-input has-toggle" 
                                        placeholder="At least 6 characters" 
                                        required 
                                        minlength="6"
                                        autocomplete="new-password"
                                        autofocus
                                    >
                                    <button 
                                        type="button" 
                                        class="password-toggle-btn" 
                                        onclick="togglePasswordVisibility('password', this)" 
                                        aria-label="Toggle password visibility" 
                                        title="Show password"
                                        tabindex="-1"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">Confirm New Password</label>
                                <div class="password-field-wrap position-relative">
                                    <input 
                                        type="password" 
                                        id="password_confirm" 
                                        name="password_confirm" 
                                        class="form-control bolso-input has-toggle" 
                                        placeholder="Repeat new password" 
                                        required 
                                        minlength="6"
                                        autocomplete="new-password"
                                    >
                                    <button 
                                        type="button" 
                                        class="password-toggle-btn" 
                                        onclick="togglePasswordVisibility('password_confirm', this)" 
                                        aria-label="Toggle password visibility" 
                                        title="Show password"
                                        tabindex="-1"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-bolso w-100 py-3 mb-3">
                                Save New Password <i class="bi bi-check2 ms-2"></i>
                            </button>

                            <div class="text-center mt-3">
                                <a href="login.php" class="text-link dark-link">&larr; Back to Sign In</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function togglePasswordVisibility(fieldId, btn) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    var icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
        btn.setAttribute('title', 'Hide password');
        btn.setAttribute('aria-label', 'Hide password');
    } else {
        field.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
        btn.setAttribute('title', 'Show password');
        btn.setAttribute('aria-label', 'Show password');
    }
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
