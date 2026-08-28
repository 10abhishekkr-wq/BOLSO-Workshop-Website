<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = bolso_db();
$registrations = [];
$stats = ['total' => 0, 'online' => 0, 'offline' => 0, 'pending' => 0];
$error = null;

if ($pdo) {
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_admin_csrf($_POST['csrf_token'] ?? null)) {
            $error = 'Your session expired. Refresh the page and try again.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = (string)($_POST['action'] ?? '');
            $registrationId = filter_var($_POST['registration_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$registrationId) {
                $error = 'That registration could not be found.';
            } elseif ($action === 'update_payment') {
                $status = (string)($_POST['payment_status'] ?? '');
                if (!in_array($status, ['pending', 'paid', 'failed', 'refunded'], true)) {
                    $error = 'That payment status is not valid.';
                } else {
                    $statement = $pdo->prepare('UPDATE registrations SET payment_status = :status WHERE id = :id');
                    $statement->execute([':status' => $status, ':id' => $registrationId]);
                    admin_flash('success', 'Payment status updated.');
                    header('Location: dashboard.php');
                    exit;
                }
            } elseif ($action === 'delete_registration') {
                $statement = $pdo->prepare('DELETE FROM registrations WHERE id = :id');
                $statement->execute([':id' => $registrationId]);
                admin_flash('success', 'Registration deleted.');
                header('Location: dashboard.php');
                exit;
            }
        }
        $registrations = $pdo->query('SELECT * FROM registrations ORDER BY registration_date DESC')->fetchAll();
        $stats['total'] = count($registrations);
        foreach ($registrations as $registration) {
            if ($registration['mode'] === 'online') $stats['online']++;
            if ($registration['mode'] === 'offline') $stats['offline']++;
            if ($registration['payment_status'] === 'pending') $stats['pending']++;
        }
    } catch (PDOException $exception) {
        error_log('BOLSO dashboard read failed: ' . $exception->getMessage());
        $error = 'Registrations could not be loaded.';
    }
} else {
    $error = 'Connect MySQL to load registration data.';
}

$pageTitle = 'Admin dashboard';
$isAdminArea = true;
require __DIR__ . '/../includes/header.php';
?>
<main class="admin-dashboard">
    <div class="container">
        <div class="dashboard-heading"><div><span class="eyebrow">BOLSO studio / overview</span><h1>Good morning, <?= htmlspecialchars((string)($_SESSION['admin_username'] ?? 'admin'), ENT_QUOTES, 'UTF-8') ?>.</h1></div><a class="btn btn-primary-bolso" href="../registration.php">View registration form <i class="bi bi-arrow-up-right"></i></a></div>
        <?php if ($error): ?><div class="alert bolso-alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php $flash = take_admin_flash(); if ($flash): ?><div class="alert alert-success bolso-success-alert" role="alert"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <div class="dashboard-stats"><div class="stat-card stat-card-dark"><span>Total registrations</span><strong><?= $stats['total'] ?></strong><small>All time</small></div><div class="stat-card"><span>Online</span><strong><?= $stats['online'] ?></strong><small>Live Google Meet</small></div><div class="stat-card"><span>Offline</span><strong><?= $stats['offline'] ?></strong><small>In-person batches</small></div><div class="stat-card stat-card-accent"><span>Payment pending</span><strong><?= $stats['pending'] ?></strong><small>Needs follow-up</small></div></div>
        <div class="dashboard-table-head"><div><span class="eyebrow">Recent interest</span><h2>Registrations</h2></div><span class="batch-badge">Maximum 6 per batch</span></div>
        <div class="table-shell">
            <div class="table-responsive"><table class="table bolso-table align-middle"><thead><tr><th>Student</th><th>Workshop</th><th>Mode</th><th>Preferred date</th><th>Price</th><th>Payment</th><th>Registered</th><th>Manage</th></tr></thead><tbody>
            <?php if (!$registrations): ?><tr><td colspan="8" class="empty-row">No registrations yet. Your first student will appear here.</td></tr><?php endif; ?>
            <?php foreach ($registrations as $registration): ?><tr><td><strong><?= htmlspecialchars($registration['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($registration['email'], ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars($registration['whatsapp'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars($registration['workshop'], ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($registration['interest'], ENT_QUOTES, 'UTF-8') ?></small></td><td><span class="mode-pill <?= $registration['mode'] === 'offline' ? 'offline' : 'online' ?>"><?= htmlspecialchars($registration['mode'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= htmlspecialchars($registration['preferred_date'], ENT_QUOTES, 'UTF-8') ?></td><td>₹<?= number_format((int)$registration['price']) ?></td><td><span class="status-pill <?= htmlspecialchars($registration['payment_status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($registration['payment_status'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= htmlspecialchars(date('d M Y', strtotime($registration['registration_date'])), ENT_QUOTES, 'UTF-8') ?></td><td><div class="manage-actions"><form method="post" class="payment-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="update_payment"><input type="hidden" name="registration_id" value="<?= (int)$registration['id'] ?>"><select name="payment_status" aria-label="Payment status for <?= htmlspecialchars($registration['name'], ENT_QUOTES, 'UTF-8') ?>"><option value="pending" <?= $registration['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option><option value="paid" <?= $registration['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option><option value="failed" <?= $registration['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option><option value="refunded" <?= $registration['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option></select><button type="submit" title="Update payment status"><i class="bi bi-check2"></i></button></form><form method="post" onsubmit="return confirm('Delete this registration? This cannot be undone.');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="delete_registration"><input type="hidden" name="registration_id" value="<?= (int)$registration['id'] ?>"><button class="delete-button" type="submit" title="Delete registration"><i class="bi bi-trash3"></i></button></form></div></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
        <p class="dashboard-footnote"><i class="bi bi-info-circle"></i> Update payment status directly in MySQL or connect the chosen payment provider when you’re ready. No payment is simulated here.</p>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>