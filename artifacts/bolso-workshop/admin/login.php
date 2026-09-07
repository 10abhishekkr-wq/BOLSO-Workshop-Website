<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $pdo = bolso_db();

    if ($pdo) {
        $statement = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
        $statement->execute([':username' => $username]);
        $admin = $statement->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'That login did not match. Please check your details.';
}

$pageTitle = 'Admin login';
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
        <span class="eyebrow">BOLSO studio</span>
        <h1>Welcome<br><em>back.</em></h1>
        <p>Sign in to manage registrations and batch details.</p>
        <?php if ($error): ?><div class="alert bolso-alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="username">Username</label><input id="username" name="username" autocomplete="username" required>
            <label for="password">Password</label>
            <div style="position: relative; width: 100%;">
                <input id="password" type="password" name="password" autocomplete="current-password" required style="padding-right: 44px; width: 100%;">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility" title="Show password" tabindex="-1" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--ink-soft); cursor: pointer; font-size: 18px; padding: 4px; display: flex; align-items: center; justify-content: center; z-index: 3;">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <button class="btn btn-primary-bolso w-100 mt-3" type="submit">Sign in <i class="bi bi-arrow-right"></i></button>
        </form>
        <a class="text-link dark-link mt-4 d-inline-block" href="../index.php"><i class="bi bi-arrow-left"></i> Back to site</a>
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