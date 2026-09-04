<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_admin();

$pdo = bolso_db();
$error = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_admin_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session security token expired. Please refresh.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        // 1. Send Test Notification
        if ($action === 'test_dispatch') {
            $testEmail = trim((string)($_POST['test_email'] ?? '10abhishekkr@gmail.com'));
            $testPhone = trim((string)($_POST['test_phone'] ?? '9341469219'));

            $dummyReg = [
                'id' => 9999,
                'name' => 'Demo Student',
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
            admin_flash('success', "Test alerts dispatched! Admin alert sent to 10abhishekkr@gmail.com and WhatsApp 9341469219. Customer clarification email & WhatsApp link generated.");
            header('Location: notifications.php');
            exit;
        }

        // 2. Clear Simulated Logs
        elseif ($action === 'clear_simulated') {
            if ($pdo) {
                try {
                    $pdo->exec("DELETE FROM notifications_log WHERE status = 'simulated'");
                    admin_flash('success', 'Simulated notification records cleaned.');
                    header('Location: notifications.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Failed to clear simulated logs.';
                }
            }
        }
    }
}

// Fetch stats and logs
$stats = bolso_get_notification_stats();

$filterChannel = trim((string)($_GET['channel'] ?? ''));
$filterRecipient = trim((string)($_GET['recipient'] ?? ''));
$filterStatus = trim((string)($_GET['status'] ?? ''));
$filterSearch = trim((string)($_GET['search'] ?? ''));

$filterParams = [];
if ($filterChannel !== '') $filterParams['channel'] = $filterChannel;
if ($filterRecipient !== '') $filterParams['recipient_type'] = $filterRecipient;
if ($filterStatus !== '') $filterParams['status'] = $filterStatus;
if ($filterSearch !== '') $filterParams['search'] = $filterSearch;

$logs = bolso_get_notifications(150, 0, $filterParams);

$pageTitle = 'Notification Logs & Alerts';
$isAdminArea = true;
$adminActiveTab = 'notifications';
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-page-container">
    <div class="container-fluid px-lg-4">
        <!-- Heading -->
        <div class="dashboard-heading flex-wrap gap-3">
            <div>
                <span class="eyebrow">Communications & Alerts</span>
                <h1>Notification Logs & Dispatch Center</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-primary-bolso" data-bs-toggle="modal" data-bs-target="#testAlertModal">
                    <i class="bi bi-send-check me-1"></i> Send Test Alert
                </button>
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

        <!-- Active Routing Banner -->
        <div class="alert alert-light border shadow-sm p-3 mt-4 mb-4 rounded-3">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="bi bi-shield-check me-1"></i> Active Notification Rules
                        </span>
                        <span class="text-muted small">Automatic 4-Way Dispatch on Payment Success</span>
                    </div>
                    <div class="small text-secondary">
                        <strong>Admin Alerts:</strong> Email &rarr; <code>10abhishekkr@gmail.com</code> &bull; WhatsApp &rarr; <code>+91 9341469219</code><br>
                        <strong>Customer Message:</strong> Contains confirmation &amp; explicit reassurance: <em>"your timing for the workshop will be given to you very soon"</em>.
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span class="badge bg-light text-dark border px-3 py-2 text-start">
                        <small class="text-muted d-block" style="font-size: 10px;">ENGINE STATUS</small>
                        <i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i> SMTP Socket / Webhook Ready
                    </span>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 border rounded bg-white shadow-sm h-100">
                    <span class="metric-label text-muted small text-uppercase fw-semibold">Total Dispatched</span>
                    <div class="metric-value fs-3 fw-bold text-dark mt-1"><?= number_format($stats['total']) ?></div>
                    <small class="text-muted" style="font-size: 11px;">All channels</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 border rounded bg-white shadow-sm h-100">
                    <span class="metric-label text-muted small text-uppercase fw-semibold">Email Alerts</span>
                    <div class="metric-value fs-3 fw-bold text-primary mt-1"><?= number_format($stats['email_count']) ?></div>
                    <small class="text-muted" style="font-size: 11px;">Customer & Admin mailers</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 border rounded bg-white shadow-sm h-100">
                    <span class="metric-label text-muted small text-uppercase fw-semibold">WhatsApp Messages</span>
                    <div class="metric-value fs-3 fw-bold text-success mt-1"><?= number_format($stats['whatsapp_count']) ?></div>
                    <small class="text-muted" style="font-size: 11px;">Direct links & gateway API</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card p-3 border rounded bg-white shadow-sm h-100">
                    <span class="metric-label text-muted small text-uppercase fw-semibold">Today's Traffic</span>
                    <div class="metric-value fs-3 fw-bold text-warning-emphasis mt-1"><?= number_format($stats['today_count']) ?></div>
                    <small class="text-muted" style="font-size: 11px;">Dispatched today</small>
                </div>
            </div>
        </div>

        <!-- Filter and Search Toolbar -->
        <div class="admin-card p-3 mb-3">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search email, phone, subject..." value="<?= htmlspecialchars($filterSearch, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="channel" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Channels</option>
                        <option value="email" <?= $filterChannel === 'email' ? 'selected' : '' ?>>Email</option>
                        <option value="whatsapp" <?= $filterChannel === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="recipient" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Recipients</option>
                        <option value="customer" <?= $filterRecipient === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="admin" <?= $filterRecipient === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="sent" <?= $filterStatus === 'sent' ? 'selected' : '' ?>>Sent</option>
                        <option value="simulated" <?= $filterStatus === 'simulated' ? 'selected' : '' ?>>Simulated</option>
                        <option value="ready" <?= $filterStatus === 'ready' ? 'selected' : '' ?>>Ready</option>
                        <option value="failed" <?= $filterStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 text-end">
                    <a href="notifications.php" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="admin-card p-0">
            <div class="table-responsive">
                <table class="table bolso-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Date & Time</th>
                            <th>Recipient</th>
                            <th>Channel</th>
                            <th>Destination</th>
                            <th>Subject / Preview</th>
                            <th>Linked Student</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                No notification logs found matching the selected criteria.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($logs as $log): 
                        $isEmail = $log['channel'] === 'email';
                        $isAdmin = $log['recipient_type'] === 'admin';
                        $cleanPhone = bolso_clean_phone($log['destination']);
                        $waDirectLink = 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($log['message']);
                    ?>
                        <tr>
                            <td>
                                <small class="fw-bold d-block"><?= date('d M Y', strtotime($log['created_at'])) ?></small>
                                <small class="text-muted" style="font-size: 11px;"><?= date('h:i:s A', strtotime($log['created_at'])) ?></small>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <span class="badge bg-dark text-white px-2 py-1">
                                        <i class="bi bi-shield-lock me-1"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="bi bi-person me-1"></i> Customer
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isEmail): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                        <i class="bi bi-envelope me-1"></i> Email
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['destination'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($log['subject'] ?? substr($log['message'], 0, 100), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($isEmail): ?>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($log['subject'] ?? 'Notification', ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= htmlspecialchars(substr($log['message'], 0, 70), ENT_QUOTES, 'UTF-8') ?>...</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($log['registration_id'])): ?>
                                    <a href="registrations.php?search=<?= (int)$log['registration_id'] ?>" class="text-decoration-none fw-semibold">
                                        #<?= (int)$log['registration_id'] ?> &bull; <?= htmlspecialchars($log['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">System / Test</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'sent'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Sent
                                    </span>
                                <?php elseif ($log['status'] === 'simulated'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" title="Saved & logged safely without external credentials">
                                        <i class="bi bi-file-earmark-text me-1"></i> Simulated
                                    </span>
                                <?php elseif ($log['status'] === 'ready'): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-link-45deg me-1"></i> Link Ready
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="<?= htmlspecialchars($log['error_message'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Failed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <!-- View Message Button -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1 px-2" title="Preview Message"
                                            onclick='previewLog(<?= json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- If WhatsApp, direct wa.me link -->
                                    <?php if (!$isEmail): ?>
                                        <a href="<?= $waDirectLink ?>" target="_blank" class="btn btn-sm btn-outline-success p-1 px-2" title="Open in WhatsApp Web">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- MODAL: Preview Notification -->
<div class="modal fade bolso-modal" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="eyebrow" id="prev_channel_badge">Email Preview</span>
                    <h5 class="modal-title" id="prev_subject">Notification Preview</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-2 mb-3 bg-light rounded border small d-flex flex-wrap justify-content-between gap-2">
                    <div><strong>To:</strong> <span id="prev_destination">-</span></div>
                    <div><strong>Recipient:</strong> <span id="prev_recipient_type">-</span></div>
                    <div><strong>Status:</strong> <span id="prev_status">-</span></div>
                    <div><strong>Date:</strong> <span id="prev_date">-</span></div>
                </div>

                <!-- Container for HTML Email -->
                <div id="prev_email_wrap" style="display:none;">
                    <div class="border rounded" style="min-height: 380px; max-height: 520px; overflow-y: auto;">
                        <iframe id="prev_email_frame" style="width:100%; height:450px; border:none;"></iframe>
                    </div>
                </div>

                <!-- Container for WhatsApp Chat Bubble -->
                <div id="prev_whatsapp_wrap" style="display:none;">
                    <div class="p-3 rounded-3" style="background-color: #E5DDD5; max-height: 480px; overflow-y: auto;">
                        <div class="bg-white p-3 rounded-3 shadow-sm" style="max-width: 85%; white-space: pre-wrap; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px; line-height: 1.5; color: #111B21; border-top-left-radius: 0 !important;" id="prev_whatsapp_text"></div>
                    </div>
                    <div class="mt-3 text-end">
                        <a id="prev_wa_open_btn" href="#" target="_blank" class="btn btn-success btn-sm">
                            <i class="bi bi-whatsapp me-1"></i> Send / Open in WhatsApp Web
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Send Test Alert -->
<div class="modal fade bolso-modal" id="testAlertModal" tabindex="-1" aria-labelledby="testAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="test_dispatch">

                <div class="modal-header">
                    <div>
                        <span class="eyebrow">Diagnostic Dispatch</span>
                        <h5 class="modal-title" id="testAlertModalLabel">Trigger Test Notification</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        This will simulate a successful registration payment. It sends the thankful payment confirmation to the student email/WhatsApp, and alerts the admin at <strong>10abhishekkr@gmail.com</strong> and <strong>9341469219</strong>.
                    </p>

                    <div class="mb-3">
                        <label class="form-label" for="diag_email">Test Student Email *</label>
                        <input type="email" class="form-control" id="diag_email" name="test_email" value="10abhishekkr@gmail.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="diag_phone">Test Student WhatsApp *</label>
                        <input type="text" class="form-control" id="diag_phone" name="test_phone" value="9341469219" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-bolso">
                        <i class="bi bi-bell me-1"></i> Dispatch Test Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewLog(log) {
    document.getElementById('prev_channel_badge').innerText = (log.channel || 'EMAIL').toUpperCase() + ' PREVIEW';
    document.getElementById('prev_subject').innerText = log.subject || 'Notification Content';
    document.getElementById('prev_destination').innerText = log.destination || '-';
    document.getElementById('prev_recipient_type').innerText = (log.recipient_type || 'Customer').toUpperCase();
    document.getElementById('prev_status').innerText = (log.status || 'SENT').toUpperCase();
    document.getElementById('prev_date').innerText = log.created_at || '-';

    const emailWrap = document.getElementById('prev_email_wrap');
    const waWrap = document.getElementById('prev_whatsapp_wrap');
    const iframe = document.getElementById('prev_email_frame');
    const waText = document.getElementById('prev_whatsapp_text');
    const waBtn = document.getElementById('prev_wa_open_btn');

    if (log.channel === 'email') {
        waWrap.style.display = 'none';
        emailWrap.style.display = 'block';
        iframe.srcdoc = log.message || '<p>No content</p>';
    } else {
        emailWrap.style.display = 'none';
        waWrap.style.display = 'block';
        waText.innerText = log.message || '';
        const clean = (log.destination || '').replace(/\D/g, '');
        waBtn.href = 'https://wa.me/' + clean + '?text=' + encodeURIComponent(log.message || '');
    }

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
