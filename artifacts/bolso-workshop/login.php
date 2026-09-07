<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (is_user_logged_in()) {
    header('Location: my-workshops.php');
    exit;
}

$errors = [];
$flash = take_user_flash();
$redirect = trim((string)($_GET['redirect'] ?? $_POST['redirect'] ?? ''));
if ($redirect !== '' && (str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://') || str_starts_with($redirect, '//'))) {
    $redirect = 'my-workshops.php';
}

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string)($_POST['csrf_token'] ?? '');
    if (!verify_user_csrf($csrfToken)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    $email = trim(strtolower((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    }
    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        $pdo = bolso_db();
        if (!$pdo) {
            $errors[] = 'Database connection error. Please try again shortly.';
        } else {
            $stmt = $pdo->prepare('SELECT id, name, email, whatsapp, password_hash FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, (string)$user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = (string)$user['name'];
                $_SESSION['user_email'] = (string)$user['email'];
                $_SESSION['user_whatsapp'] = (string)$user['whatsapp'];

                // Automatically link any past registrations placed with this email
                try {
                    $linkStmt = $pdo->prepare('UPDATE registrations SET user_id = :uid WHERE LOWER(email) = LOWER(:email) AND user_id IS NULL');
                    $linkStmt->execute([':uid' => $user['id'], ':email' => $user['email']]);
                } catch (PDOException $e) {
                    error_log('Failed linking existing registrations: ' . $e->getMessage());
                }

                $dest = $redirect !== '' ? $redirect : 'my-workshops.php';
                header('Location: ' . $dest);
                exit;
            } else {
                $errors[] = 'Incorrect email address or password. Please try again.';
            }
        }
    }
}

$pageTitle = 'Sign In';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<main class="user-auth-page section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="user-auth-card">
                    <div class="auth-card-header text-center mb-4">
                        <span class="eyebrow">Welcome Back</span>
                        <h1 class="auth-title mt-2">Sign in to<br><em>BOLSO.</em></h1>
                        <p class="auth-subtitle">Access your enrolled workshops, view class schedules, and connect with your instructors.</p>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'bolso-success-alert' : 'bolso-alert' ?> mb-4" role="alert">
                            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert bolso-alert mb-4" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php<?= $redirect !== '' ? '?redirect=' . urlencode($redirect) : '' ?>" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(user_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($redirect !== ''): ?>
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control bolso-input" placeholder="you@example.com" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email" autofocus>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label mb-0">Password</label>
                            </div>
                            <div class="input-icon-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control bolso-input has-toggle" placeholder="Enter your password" required autocomplete="current-password">
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility" title="Show password" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-bolso w-100 py-3 mb-3">
                            Sign In <i class="bi bi-arrow-right ms-1"></i>
                        </button>

                        <div class="auth-switch text-center pt-2">
                            <span class="text-muted">Don't have a student account?</span>
                            <a href="signup.php<?= $redirect !== '' ? '?redirect=' . urlencode($redirect) : '' ?>" class="auth-switch-link ms-1">
                                Create one here <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
<?php require __DIR__ . '/includes/footer.php'; ?>
