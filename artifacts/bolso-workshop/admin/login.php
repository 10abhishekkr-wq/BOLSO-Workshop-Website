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
        <div class="admin-login-mark">✦</div>
        <span class="eyebrow">BOLSO studio</span>
        <h1>Welcome<br><em>back.</em></h1>
        <p>Sign in to manage registrations and batch details.</p>
        <?php if ($error): ?><div class="alert bolso-alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="username">Username</label><input id="username" name="username" autocomplete="username" required>
            <label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required>
            <button class="btn btn-primary-bolso w-100 mt-3" type="submit">Sign in <i class="bi bi-arrow-right"></i></button>
        </form>
        <a class="text-link dark-link mt-4 d-inline-block" href="../index.php"><i class="bi bi-arrow-left"></i> Back to site</a>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>