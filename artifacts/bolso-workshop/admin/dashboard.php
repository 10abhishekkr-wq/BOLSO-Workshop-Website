<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = bolso_db();
$stats = [
    'total' => 0,
    'online' => 0,
    'offline' => 0,
    'pending' => 0,
    'paid' => 0,
    'revenue' => 0,
    'workshops' => 0,
];
$recentRegistrations = [];
$recentPayments = [];
$error = null;

if ($pdo) {
    try {
        // Aggregate statistics
        $regQuery = $pdo->query('SELECT mode, payment_status, price FROM registrations');
        $allRegs = $regQuery->fetchAll();
        $stats['total'] = count($allRegs);
        foreach ($allRegs as $r) {
            if ($r['mode'] === 'online') $stats['online']++;
            if ($r['mode'] === 'offline') $stats['offline']++;
            if ($r['payment_status'] === 'pending') $stats['pending']++;
            if ($r['payment_status'] === 'paid') {
                $stats['paid']++;
                $stats['revenue'] += (int)$r['price'];
            }
        }

        // Count active workshops
        $wsCount = $pdo->query('SELECT COUNT(*) FROM workshops WHERE active = 1')->fetchColumn();
        $stats['workshops'] = (int)$wsCount;

        // Fetch latest 6 registrations
        $recentRegistrations = $pdo->query(
            'SELECT r.*, p.provider_payment_id 
             FROM registrations r 
             LEFT JOIN payments p ON p.registration_id = r.id 
             ORDER BY r.registration_date DESC 
             LIMIT 6'
        )->fetchAll();

        // Fetch latest 5 payment transactions
        $recentPayments = $pdo->query(
            'SELECT p.*, r.name as student_name, r.workshop, r.mode 
             FROM payments p 
             JOIN registrations r ON r.id = p.registration_id 
             ORDER BY p.created_at DESC 
             LIMIT 5'
        )->fetchAll();

    } catch (PDOException $e) {
        error_log('Dashboard metrics load error: ' . $e->getMessage());
        $error = 'Dashboard data could not be fully loaded.';
    }
} else {
    $error = 'Database connection is inactive. Check config/config.local.php.';
}

$pageTitle = 'Studio Overview';
$isAdminArea = true;
$adminActiveTab = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-dashboard admin-page-container">
    <div class="container-fluid px-lg-4">
        <div class="dashboard-heading">
            <div>
                <span class="eyebrow">BOLSO Studio Dashboard</span>
                <h1>Overview & Insights</h1>
            </div>
            <div class="dash-actions">
                <a href="registrations.php?action=new" class="btn btn-primary-bolso">
                    <i class="bi bi-person-plus"></i> Add Walk-in Student
                </a>
                <a href="registrations.php?export=csv" class="btn btn-outline-bolso" title="Export all registrations">
                    <i class="bi bi-download"></i> Export CSV
                </a>
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

        <!-- Key Metrics Cards -->
        <div class="dashboard-stats mt-4">
            <div class="stat-card stat-card-revenue">
                <span>Total Confirmed Revenue</span>
                <strong>₹<?= number_format($stats['revenue']) ?></strong>
                <small><?= $stats['paid'] ?> paid registrations</small>
            </div>
            <div class="stat-card stat-card-dark">
                <span>Total Registrations</span>
                <strong><?= $stats['total'] ?></strong>
                <small>All-time students</small>
            </div>
            <div class="stat-card">
                <span>Online vs Offline</span>
                <strong><?= $stats['online'] ?> / <?= $stats['offline'] ?></strong>
                <small><?= $stats['online'] ?> Online &bull; <?= $stats['offline'] ?> Studio</small>
            </div>
            <div class="stat-card stat-card-accent">
                <span>Pending Follow-ups</span>
                <strong><?= $stats['pending'] ?></strong>
                <small>Awaiting payment or confirmation</small>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <!-- Recent Registrations Card -->
            <div class="col-lg-8">
                <div class="admin-card h-100">
                    <div class="admin-card-header">
                        <div>
                            <span class="eyebrow">Latest Enrolments</span>
                            <h3 class="admin-card-title">Recent Registrations</h3>
                        </div>
                        <a href="registrations.php" class="btn btn-sm btn-outline-bolso">
                            View All (<?= $stats['total'] ?>) <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table bolso-table align-middle">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Workshop</th>
                                    <th>Mode</th>
                                    <th>Investment</th>
                                    <th>Payment</th>
                                    <th>Registered</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($recentRegistrations)): ?>
                                <tr><td colspan="7" class="empty-row">No registrations yet. Your first student will appear here.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentRegistrations as $reg): 
                                    $cleanPhone = preg_replace('/\D/', '', $reg['whatsapp']);
                                    $waText = rawurlencode("Hello {$reg['name']}! Greetings from BOLSO regarding your {$reg['workshop']} registration.");
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($reg['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <small><?= htmlspecialchars($reg['email'], ENT_QUOTES, 'UTF-8') ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($reg['workshop'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <small><?= htmlspecialchars($reg['preferred_date'], ENT_QUOTES, 'UTF-8') ?></small>
                                    </td>
                                    <td>
                                        <span class="mode-pill <?= $reg['mode'] === 'offline' ? 'offline' : 'online' ?>">
                                            <?= htmlspecialchars($reg['mode'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td><strong>₹<?= number_format((int)$reg['price']) ?></strong></td>
                                    <td>
                                        <span class="status-pill <?= htmlspecialchars($reg['payment_status'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($reg['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($reg['registration_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waText ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success px-2 py-1" title="Chat on WhatsApp">
                                            <i class="bi bi-whatsapp"></i> Chat
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Management & Workshop Summary -->
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-4">
                    <!-- Quick Actions Card -->
                    <div class="admin-card">
                        <span class="eyebrow">Studio Shortcuts</span>
                        <h3 class="admin-card-title mb-3">Quick Actions</h3>
                        <div class="d-flex flex-column gap-2">
                            <a href="registrations.php?action=new" class="btn btn-outline-bolso w-100 justify-content-between">
                                <span><i class="bi bi-person-plus me-1"></i> Register Walk-in Student</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="workshops.php" class="btn btn-outline-bolso w-100 justify-content-between">
                                <span><i class="bi bi-tags me-1"></i> Edit Workshop Prices</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="payments.php" class="btn btn-outline-bolso w-100 justify-content-between">
                                <span><i class="bi bi-credit-card me-1"></i> View Payment Records</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                            <a href="settings.php" class="btn btn-outline-bolso w-100 justify-content-between">
                                <span><i class="bi bi-shield-lock me-1"></i> Security & Password</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Transactions Feed -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <span class="eyebrow">Transactions</span>
                                <h3 class="admin-card-title">Razorpay Activity</h3>
                            </div>
                            <a href="payments.php" class="text-link" style="font-size: 11px;">View Ledger</a>
                        </div>

                        <?php if (empty($recentPayments)): ?>
                            <p class="text-muted small mb-0">No transaction logs recorded yet.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($recentPayments as $pay): ?>
                                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                        <div>
                                            <strong class="d-block" style="font-size: 13px;"><?= htmlspecialchars($pay['student_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small class="text-muted" style="font-family: monospace; font-size: 11px;">
                                                <?= htmlspecialchars($pay['provider_payment_id'] ?? 'Manual', ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success" style="font-size: 13px;">+₹<?= number_format((int)$pay['amount']) ?></strong>
                                            <small class="d-block text-muted" style="font-size: 10px;"><?= htmlspecialchars(date('d M, H:i', strtotime($pay['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>