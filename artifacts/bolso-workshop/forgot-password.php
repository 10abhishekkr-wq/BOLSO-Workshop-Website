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

$errors = [];
$successMessage = '';
$devResetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_user_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your security session expired. Please refresh the page and try again.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $pdo = bolso_db();
            if (!$pdo) {
                $errors[] = 'Database connection error. Please try again later.';
            } else {
                try {
                    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
                    $stmt->execute([':email' => $email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        $token = bolso_create_password_reset('user', (int)$user['id'], (string)$user['email']);
                        if ($token) {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                            $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                            $resetLink = "{$scheme}://{$host}{$baseDir}/reset-password.php?token=" . urlencode($token);

                            $mailResult = bolso_send_password_reset_email('user', (string)$user['email'], (string)$user['name'], $resetLink);

                            // In local environment or if mail cannot dispatch directly, provide direct recovery link
                            $isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);
                            if ($isLocal || empty($mailResult['success'])) {
                                $devResetLink = $resetLink;
                            }
                        }
                    }

                    $successMessage = 'If an account exists with that email address, password reset instructions have been sent. Please check your inbox.';
                } catch (PDOException $e) {
                    error_log('Forgot password error: ' . $e->getMessage());
                    $errors[] = 'An error occurred while processing your request. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Forgot Password';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-section section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <span class="auth-eyebrow">Account Recovery</span>
                        <h1 class="auth-title">Reset Your <em>Password</em></h1>
                        <p class="auth-subtitle">Enter your registered email and we'll send you instructions to choose a new password.</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger py-2 px-3 mb-4 rounded-0" role="alert" style="font-size: 13px; border-left: 3px solid var(--maroon);">
                            <?php foreach ($errors as $err): ?>
                                <div><i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success py-3 px-3 mb-4 rounded-0" role="alert" style="font-size: 13.5px; border-left: 3px solid #28a745; background: rgba(40, 167, 69, 0.08); color: #155724;">
                            <div class="fw-semibold mb-1"><i class="bi bi-check2-circle me-1"></i> Email Dispatched</div>
                            <div><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>

                        <?php if ($devResetLink !== ''): ?>
                            <div class="p-3 mb-4 rounded-2" style="background: rgba(189, 92, 62, 0.08); border: 1.5px dashed var(--terracotta); font-size: 12.5px;">
                                <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock me-1"></i> Quick Reset Link</div>
                                <div class="text-muted mb-2">Ready to reset your password:</div>
                                <a href="<?= htmlspecialchars($devResetLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-primary-bolso py-1 px-3" style="font-size: 11px;">Proceed to Reset Password &rarr;</a>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="login.php" class="text-link dark-link">&larr; Return to Sign In</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="forgot-password.php" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(user_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-4">
                                <label for="email" class="form-label">Registered Email Address</label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        class="form-control bolso-input" 
                                        placeholder="student@example.com" 
                                        required 
                                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        autocomplete="email"
                                        autofocus
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary-bolso w-100 py-3 mb-3">
                                Send Password Reset Link <i class="bi bi-arrow-right ms-2"></i>
                            </button>

                            <div class="text-center mt-3">
                                <a href="login.php" class="text-link dark-link">&larr; Remember your password? Sign in</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
