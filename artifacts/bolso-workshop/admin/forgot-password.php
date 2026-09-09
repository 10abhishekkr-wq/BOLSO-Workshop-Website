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

$error = '';
$successMessage = '';
$devResetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_admin_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session security token expired. Please refresh and try again.';
    } else {
        $identifier = trim((string)($_POST['identifier'] ?? ''));

        if ($identifier === '') {
            $error = 'Please enter your admin username or email address.';
        } else {
            $pdo = bolso_db();
            if (!$pdo) {
                $error = 'Database connection error. Please try again later.';
            } else {
                try {
                    $stmt = $pdo->prepare('SELECT id, username, email FROM admins WHERE LOWER(username) = LOWER(:id1) OR LOWER(email) = LOWER(:id2) LIMIT 1');
                    $stmt->execute([':id1' => $identifier, ':id2' => $identifier]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($admin) {
                        $targetEmail = !empty($admin['email']) ? (string)$admin['email'] : '10abhishekkr@gmail.com';
                        $token = bolso_create_password_reset('admin', (int)$admin['id'], $targetEmail);

                        if ($token) {
                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                            $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                            $resetLink = "{$scheme}://{$host}{$baseDir}/reset-password.php?token=" . urlencode($token);

                            $mailRes = bolso_send_password_reset_email('admin', $targetEmail, (string)$admin['username'], $resetLink);

                            $isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);
                            if ($isLocal || empty($mailRes['success'])) {
                                $devResetLink = $resetLink;
                            }
                        }
                    }

                    $successMessage = 'If a matching administrator account exists, recovery instructions have been sent to the registered email address.';
                } catch (PDOException $e) {
                    error_log('Admin forgot password error: ' . $e->getMessage());
                    $error = 'An error occurred while processing recovery.';
                }
            }
        }
    }
}

$pageTitle = 'Admin Password Recovery';
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
        <h1>Password<br><em>recovery.</em></h1>
        <p>Enter your administrator username or email to reset access.</p>

        <?php if ($error !== ''): ?>
            <div class="alert bolso-alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success py-3 px-3 mb-4 rounded-0" role="alert" style="font-size: 13px; border-left: 3px solid #28a745; background: rgba(40,167,69,0.08); color: #155724;">
                <div class="fw-semibold mb-1"><i class="bi bi-envelope-check me-1"></i> Recovery Dispatched</div>
                <div><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <?php if ($devResetLink !== ''): ?>
                <div class="p-3 mb-4 rounded-2" style="background: rgba(189, 92, 62, 0.08); border: 1.5px dashed var(--terracotta); font-size: 12.5px;">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock me-1"></i> Localhost Admin Reset Link</div>
                    <div class="text-muted mb-2">Ready to reset your admin password:</div>
                    <a href="<?= htmlspecialchars($devResetLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-primary-bolso py-1 px-3" style="font-size: 11px;">Proceed to Reset Admin Password &rarr;</a>
                </div>
            <?php endif; ?>

            <a class="text-link dark-link mt-3 d-inline-block" href="login.php">&larr; Back to admin login</a>
        <?php else: ?>
            <form method="post" class="admin-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                <label for="identifier">Username or Admin Email</label>
                <input 
                    id="identifier" 
                    name="identifier" 
                    placeholder="e.g. admin or 10abhishekkr@gmail.com" 
                    autocomplete="username" 
                    required 
                    value="<?= htmlspecialchars($_POST['identifier'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    autofocus
                >

                <button class="btn btn-primary-bolso w-100 mt-3" type="submit">
                    Send Recovery Link <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </form>

            <a class="text-link dark-link mt-4 d-inline-block" href="login.php">&larr; Back to admin login</a>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
