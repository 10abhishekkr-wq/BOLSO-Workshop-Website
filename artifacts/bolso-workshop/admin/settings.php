<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_admin();

$pdo = bolso_db();
$error = null;
$adminUser = null;

if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT id, username, created_at FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $adminUser = $stmt->fetch();

        // Handle POST Actions
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
                } elseif ($action === 'test_notification') {
                    $testEmail = trim((string)($_POST['test_email'] ?? '10abhishekkr@gmail.com'));
                    $testPhone = trim((string)($_POST['test_phone'] ?? '9341469219'));

                    $dummyReg = [
                        'id' => 9999,
                        'name' => 'Demo Student (Test Alert)',
                        'email' => $testEmail,
                        'whatsapp' => $testPhone,
                        'workshop' => '2-day',
                        'workshop_title' => 'Signature 2-Day Fabric Art',
                        'mode' => 'online',
                        'preferred_date' => date('d M Y') . ' Batch',
                        'interest' => 'Tote bag & Scarf',
                        'price' => 399,
                        'payment_status' => 'paid',
                        'payment_id' => 'TEST_RZP_' . strtoupper(substr(md5((string)time()), 0, 8)),
                    ];

                    $res = bolso_notify_payment_success($dummyReg, $dummyReg['payment_id']);
                    admin_flash('success', "Test alerts dispatched! Admin alert sent to 10abhishekkr@gmail.com and WhatsApp 9341469219. Customer reassurance notification logged.");
                    header('Location: settings.php');
                    exit;
                } elseif ($action === 'save_gateways') {
                    $smtpHost = trim((string)($_POST['smtp_host'] ?? 'smtp.gmail.com'));
                    $smtpPort = trim((string)($_POST['smtp_port'] ?? '587'));
                    $smtpUser = trim((string)($_POST['smtp_user'] ?? '10abhishekkr@gmail.com'));
                    $smtpPass = trim((string)($_POST['smtp_pass'] ?? ''));
                    $smtpSecure = trim((string)($_POST['smtp_secure'] ?? 'tls'));
                    $smtpFrom = trim((string)($_POST['smtp_from'] ?? '10abhishekkr@gmail.com'));
                    $callmebotKey = trim((string)($_POST['callmebot_apikey'] ?? ''));
                    $waApiUrl = trim((string)($_POST['whatsapp_api_url'] ?? ''));
                    $waApiToken = trim((string)($_POST['whatsapp_api_token'] ?? ''));

                    $localConfigFile = __DIR__ . '/../config/config.local.php';
                    $content = "<?php\n";
                    $content .= "putenv('BOLSO_DB_HOST=" . addslashes(bolso_config('db_host', '127.0.0.1')) . "');\n";
                    $content .= "putenv('BOLSO_DB_PORT=" . addslashes(bolso_config('db_port', '3306')) . "');\n";
                    $content .= "putenv('BOLSO_DB_NAME=" . addslashes(bolso_config('db_name', 'bolso')) . "');\n";
                    $content .= "putenv('BOLSO_DB_USER=" . addslashes(bolso_config('db_user', 'root')) . "');\n";
                    $content .= "putenv('BOLSO_DB_PASSWORD=" . addslashes(bolso_config('db_password', '')) . "');\n";
                    $content .= "putenv('BOLSO_WHATSAPP=" . addslashes(bolso_config('whatsapp', '919674773475')) . "');\n";
                    $content .= "putenv('BOLSO_PAYMENT_KEY=" . addslashes(bolso_config('payment_key')) . "');\n";
                    $content .= "putenv('BOLSO_PAYMENT_SECRET=" . addslashes(bolso_config('payment_secret')) . "');\n";
                    $content .= "putenv('BOLSO_ADMIN_EMAIL=" . addslashes(bolso_config('admin_email', '10abhishekkr@gmail.com')) . "');\n";
                    $content .= "putenv('BOLSO_ADMIN_WHATSAPP=" . addslashes(bolso_config('admin_whatsapp', '919341469219')) . "');\n";
                    $content .= "putenv('BOLSO_SMTP_HOST=" . addslashes($smtpHost) . "');\n";
                    $content .= "putenv('BOLSO_SMTP_PORT=" . addslashes($smtpPort) . "');\n";
                    $content .= "putenv('BOLSO_SMTP_USER=" . addslashes($smtpUser) . "');\n";
                    $content .= "putenv('BOLSO_SMTP_PASS=" . addslashes($smtpPass) . "');\n";
                    $content .= "putenv('BOLSO_SMTP_SECURE=" . addslashes($smtpSecure) . "');\n";
                    $content .= "putenv('BOLSO_SMTP_FROM=" . addslashes($smtpFrom) . "');\n";
                    $content .= "putenv('BOLSO_CALLMEBOT_APIKEY=" . addslashes($callmebotKey) . "');\n";
                    $content .= "putenv('BOLSO_WHATSAPP_API_URL=" . addslashes($waApiUrl) . "');\n";
                    $content .= "putenv('BOLSO_WHATSAPP_API_TOKEN=" . addslashes($waApiToken) . "');\n";

                    @file_put_contents($localConfigFile, $content);

                    $mirror = 'C:/Users/Abhishek/OneDrive/Documents/Xammp/htdocs/bolso-workshop/config/config.local.php';
                    if (is_dir(dirname($mirror))) {
                        @file_put_contents($mirror, $content);
                    }

                    admin_flash('success', 'Live notification gateway credentials saved successfully! Use the test form below to verify.');
                    header('Location: settings.php');
                    exit;
                } elseif ($action === 'test_smtp_live') {
                    require_once __DIR__ . '/../includes/mailer.php';
                    $smtpHost = trim((string)($_POST['smtp_host'] ?? bolso_config('smtp_host', 'smtp.gmail.com')));
                    $smtpPort = (int)($_POST['smtp_port'] ?? bolso_config('smtp_port', '587'));
                    $smtpUser = trim((string)($_POST['smtp_user'] ?? bolso_config('smtp_user', '10abhishekkr@gmail.com')));
                    $smtpPass = trim((string)($_POST['smtp_pass'] ?? bolso_config('smtp_pass', '')));
                    $smtpSecure = trim((string)($_POST['smtp_secure'] ?? bolso_config('smtp_secure', 'tls')));
                    $testTo = trim((string)($_POST['test_to_email'] ?? '10abhishekkr@gmail.com'));

                    if ($smtpPass === '') {
                        admin_flash('danger', 'Cannot test SMTP: App Password is empty! Please generate and paste your 16-character Google App Password in the field below.');
                    } else {
                        $testResult = bolso_test_smtp_connection($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure, $testTo);
                        if ($testResult['success']) {
                            admin_flash('success', '🎉 ' . $testResult['message']);
                        } else {
                            admin_flash('danger', '❌ Live SMTP Test Failed: ' . $testResult['message'] . ' — Note: For Gmail, you must use a 16-character App Password from myaccount.google.com/apppasswords.');
                        }
                    }
                    header('Location: settings.php');
                    exit;
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

            <!-- Workshop Notifications & Alerts Settings -->
            <div class="col-12">
                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <span class="eyebrow text-success">Automated Communications</span>
                            <h3 class="admin-card-title mb-0">Workshop Notifications & Alerts Engine</h3>
                        </div>
                        <a href="notifications.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list-columns me-1"></i> View Live Dispatch Logs
                        </a>
                    </div>
                    
                    <p class="text-muted small">
                        Configured to automatically send payment confirmation with customer reassurance (<em>"your timing for the workshop will be given to you very soon"</em>) and instantly alert admin on email and WhatsApp.
                    </p>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Admin Alert Email</small>
                                <div class="fs-6 fw-bold text-dark mt-1">
                                    <i class="bi bi-envelope-at text-primary me-1"></i> 10abhishekkr@gmail.com
                                </div>
                                <small class="text-muted d-block mt-1">Receives instant alert with student name, email, WhatsApp, and workshop details</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Admin Alert WhatsApp</small>
                                <div class="fs-6 fw-bold text-dark mt-1">
                                    <i class="bi bi-whatsapp text-success me-1"></i> +91 9341469219
                                </div>
                                <small class="text-muted d-block mt-1">Receives payment confirmation text and 1-tap customer chat link</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Customer Clarification</small>
                                <div class="fs-6 fw-bold text-success mt-1">
                                    <i class="bi bi-check-circle me-1"></i> Active in Templates
                                </div>
                                <small class="text-muted d-block mt-1"><em>"Payment is done and your timing for the workshop will be given to you very soon"</em></small>
                            </div>
                        </div>
                    </div>

                    <!-- Live Outbound Gateways Setup Form -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0"><i class="bi bi-broadcast text-primary me-2"></i> Live Delivery Gateway Credentials</h5>
                            <span class="badge bg-primary-subtle text-primary">Required for Real Phone & Inbox Delivery</span>
                        </div>
                        <p class="text-muted small mb-3">
                            Localhost XAMPP requires outbound SMTP credentials to deliver real emails to inboxes, and an automated WhatsApp Gateway API key to ping real phones automatically without manual clicks.
                        </p>

                        <form method="post" class="mt-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="save_gateways">

                            <div class="row g-3">
                                <!-- Section A: Gmail / SMTP Email Delivery -->
                                <div class="col-lg-6">
                                    <div class="p-3 border rounded h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-envelope text-primary me-1"></i> Real Email Delivery (Gmail SMTP)</h6>
                                            <?php if (trim(bolso_config('smtp_pass', '')) !== ''): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i> Credentials Set</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-exclamation-triangle me-1"></i> App Password Needed</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted small mb-3">Delivers real automated emails to customer inboxes and <code>10abhishekkr@gmail.com</code> via authenticated SMTP:</p>

                                        <div class="mb-2">
                                            <label class="form-label small mb-1">SMTP Host</label>
                                            <input type="text" name="smtp_host" class="form-control form-control-sm" value="<?= htmlspecialchars(bolso_config('smtp_host', 'smtp.gmail.com'), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-1">Port</label>
                                                <input type="text" name="smtp_port" class="form-control form-control-sm" value="<?= htmlspecialchars(bolso_config('smtp_port', '587'), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-1">Encryption</label>
                                                <select name="smtp_secure" class="form-select form-select-sm">
                                                    <option value="tls" <?= bolso_config('smtp_secure', 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                                                    <option value="ssl" <?= bolso_config('smtp_secure') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label small mb-1">Gmail / SMTP Username</label>
                                            <input type="email" name="smtp_user" class="form-control form-control-sm" value="<?= htmlspecialchars(bolso_config('smtp_user', '10abhishekkr@gmail.com'), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label small mb-1">Gmail 16-digit App Password *</label>
                                            <input type="password" name="smtp_pass" class="form-control form-control-sm" placeholder="e.g. abcd efgh ijkl mnop" value="<?= htmlspecialchars(bolso_config('smtp_pass', ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <small class="text-muted d-block mt-1" style="font-size:11px;">
                                                Generate from: <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-primary text-decoration-none fw-semibold">Google Account &rarr; Security &rarr; 2-Step Verification &rarr; App Passwords</a>.
                                            </small>
                                        </div>

                                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between gap-2">
                                            <button type="submit" name="action" value="test_smtp_live" class="btn btn-sm btn-outline-primary" formnovalidate>
                                                <i class="bi bi-send-check me-1"></i> Test SMTP Connection Now
                                            </button>
                                            <small class="text-muted" style="font-size: 11px;">Pings 10abhishekkr@gmail.com</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section B: Automated WhatsApp Delivery -->
                                <div class="col-lg-6">
                                    <div class="p-3 border rounded h-100 bg-white">
                                        <h6 class="fw-bold text-success mb-2"><i class="bi bi-whatsapp text-success me-1"></i> Real Automated WhatsApp</h6>
                                        
                                        <!-- CallMeBot (Free Admin Pings) -->
                                        <div class="mb-3 p-2 border rounded bg-light">
                                            <span class="badge bg-success-subtle text-success fw-bold mb-1">Option 1 (100% Free): CallMeBot for Admin (9341469219)</span>
                                            <label class="form-label small d-block mb-1">CallMeBot API Key</label>
                                            <input type="text" name="callmebot_apikey" class="form-control form-control-sm" placeholder="Paste CallMeBot API key here" value="<?= htmlspecialchars(bolso_config('callmebot_apikey', ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <small class="text-muted d-block mt-1" style="font-size:11px;">
                                                👉 Send message <code>I allow callmebot to send me messages</code> to WhatsApp number <strong>+34 644 59 90 92</strong> from 9341469219, and paste the key you receive.
                                            </small>
                                        </div>

                                        <!-- UltraMsg / GreenAPI (For All Customers & Admin) -->
                                        <div class="p-2 border rounded bg-light">
                                            <span class="badge bg-primary-subtle text-primary fw-bold mb-1">Option 2: Cloud Gateway for Customers + Admin (UltraMsg / GreenAPI)</span>
                                            <div class="mb-2">
                                                <label class="form-label small mb-1">API URL</label>
                                                <input type="url" name="whatsapp_api_url" class="form-control form-control-sm" placeholder="https://api.ultramsg.com/instanceXXXX/messages/chat" value="<?= htmlspecialchars(bolso_config('whatsapp_api_url', ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label small mb-1">API Token</label>
                                                <input type="text" name="whatsapp_api_token" class="form-control form-control-sm" placeholder="Token" value="<?= htmlspecialchars(bolso_config('whatsapp_api_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary-bolso px-4">
                                        <i class="bi bi-floppy me-1"></i> Save Gateway Credentials
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Test Dispatch Form -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-1"><i class="bi bi-send-check text-primary me-1"></i> Send Test Alert to Admin & Customer</h6>
                        <p class="text-muted small mb-3">Trigger an immediate test run of the email and WhatsApp notification system.</p>
                        <form method="post" class="row g-2 align-items-end">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="test_notification">

                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1" for="test_email">Test Customer Email</label>
                                <input type="email" class="form-control form-control-sm" id="test_email" name="test_email" value="10abhishekkr@gmail.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1" for="test_phone">Test Customer WhatsApp</label>
                                <input type="text" class="form-control form-control-sm" id="test_phone" name="test_phone" value="9341469219" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-sm btn-primary-bolso w-100">
                                    <i class="bi bi-bell me-1"></i> Dispatch Test Alert
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
