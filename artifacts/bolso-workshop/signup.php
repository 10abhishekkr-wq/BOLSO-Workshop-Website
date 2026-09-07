<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (is_user_logged_in()) {
    header('Location: my-workshops.php');
    exit;
}

$errors = [];
$redirect = trim((string)($_GET['redirect'] ?? $_POST['redirect'] ?? ''));
if ($redirect !== '' && (str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://') || str_starts_with($redirect, '//'))) {
    $redirect = 'my-workshops.php';
}

$name = '';
$email = '';
$whatsapp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string)($_POST['csrf_token'] ?? '');
    if (!verify_user_csrf($csrfToken)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim(strtolower((string)($_POST['email'] ?? '')));
    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($whatsapp === '') {
        $errors[] = 'Please enter your WhatsApp phone number.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match. Please re-enter your password.';
    }

    if (empty($errors)) {
        $pdo = bolso_db();
        if (!$pdo) {
            $errors[] = 'Database connection error. Please try again shortly.';
        } else {
            // Check if email already registered
            $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                $loginUrl = 'login.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : '');
                $errors[] = 'An account with this email address already exists. <a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '" style="text-decoration: underline; font-weight: 600; color: inherit;">Please sign in here</a>.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare(
                    'INSERT INTO users (name, email, whatsapp, password_hash) VALUES (:name, :email, :whatsapp, :hash)'
                );
                $insertStmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':whatsapp' => $whatsapp,
                    ':hash' => $passwordHash,
                ]);

                $userId = (int)$pdo->lastInsertId();

                // Automatically link any past registrations placed with this email
                try {
                    $linkStmt = $pdo->prepare('UPDATE registrations SET user_id = :uid WHERE LOWER(email) = LOWER(:email) AND user_id IS NULL');
                    $linkStmt->execute([':uid' => $userId, ':email' => $email]);
                } catch (PDOException $e) {
                    error_log('Failed linking existing registrations: ' . $e->getMessage());
                }

                // Log the student in
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_whatsapp'] = $whatsapp;

                user_flash('success', 'Welcome to BOLSO, ' . $name . '! Your account is now active.');

                $dest = $redirect !== '' ? $redirect : 'my-workshops.php';
                header('Location: ' . $dest);
                exit;
            }
        }
    }
}

$pageTitle = 'Create Account';
$activePage = 'signup';
require __DIR__ . '/includes/header.php';
?>
<main class="user-auth-page section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="user-auth-card">
                    <div class="auth-card-header text-center mb-4">
                        <span class="eyebrow">Student Portal</span>
                        <h1 class="auth-title mt-2">Create your<br><em>account.</em></h1>
                        <p class="auth-subtitle">Join BOLSO to manage your workshop enrollments, track timing slots, and access course resources.</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert bolso-alert mb-4" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error /* intentional HTML allowed for login link */ ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="signup.php<?= $redirect !== '' ? '?redirect=' . urlencode($redirect) : '' ?>" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(user_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($redirect !== ''): ?>
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>

                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" id="name" name="name" class="form-control bolso-input" placeholder="Your full name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control bolso-input" placeholder="you@example.com" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email">
                            </div>
                            <small class="form-hint">Used for workshop confirmations and login.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="whatsapp" class="form-label">WhatsApp Number</label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-whatsapp input-icon"></i>
                                <input type="tel" id="whatsapp" name="whatsapp" class="form-control bolso-input" placeholder="e.g. 9876543210" value="<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <small class="form-hint">For batch updates and direct artist guidance.</small>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-sm-6 form-group">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-lock input-icon"></i>
                                    <input type="password" id="password" name="password" class="form-control bolso-input" placeholder="At least 6 chars" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="password_confirm" class="form-label">Confirm Password</label>
                                <div class="input-icon-wrap">
                                    <i class="bi bi-shield-check input-icon"></i>
                                    <input type="password" id="password_confirm" name="password_confirm" class="form-control bolso-input" placeholder="Repeat password" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-bolso w-100 py-3 mb-3">
                            Create Account <i class="bi bi-arrow-right ms-1"></i>
                        </button>

                        <div class="auth-switch text-center pt-2">
                            <span class="text-muted">Already have a BOLSO account?</span>
                            <a href="login.php<?= $redirect !== '' ? '?redirect=' . urlencode($redirect) : '' ?>" class="auth-switch-link ms-1">
                                Sign in here <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
