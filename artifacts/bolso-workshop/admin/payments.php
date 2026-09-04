<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$pdo = bolso_db();
$error = null;
$payments = [];
$totalGatewayRevenue = 0;
$totalTransactions = 0;

if ($pdo) {
    try {
        // Fetch all payment transactions joined with registration data
        $payments = $pdo->query(
            'SELECT p.*, r.name as student_name, r.email as student_email, r.whatsapp, r.workshop, r.mode 
             FROM payments p 
             LEFT JOIN registrations r ON r.id = p.registration_id 
             ORDER BY p.created_at DESC'
        )->fetchAll();

        $totalTransactions = count($payments);
        foreach ($payments as $p) {
            if ($p['status'] === 'paid') {
                $totalGatewayRevenue += (float)$p['amount'];
            }
        }
    } catch (PDOException $e) {
        error_log('Payments admin load error: ' . $e->getMessage());
        $error = 'Could not load payment records.';
    }
}

$pageTitle = 'Payments Ledger';
$isAdminArea = true;
$adminActiveTab = 'payments';
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-page-container">
    <div class="container-fluid px-lg-4">
        <div class="dashboard-heading">
            <div>
                <span class="eyebrow">BOLSO Studio / Financial Ledger</span>
                <h1>Payment Transactions</h1>
            </div>
            <div class="dash-actions">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-shield-check text-success"></i> Gateway: Razorpay Secure
                </span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert bolso-alert mt-4" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <!-- Financial Summary Cards -->
        <div class="dashboard-stats mt-4">
            <div class="stat-card stat-card-revenue">
                <span>Total Recorded Revenue</span>
                <strong>₹<?= number_format($totalGatewayRevenue) ?></strong>
                <small>From all verified transactions</small>
            </div>
            <div class="stat-card stat-card-dark">
                <span>Total Transactions</span>
                <strong><?= $totalTransactions ?></strong>
                <small>Online & manual confirmations</small>
            </div>
            <div class="stat-card">
                <span>Avg. Transaction</span>
                <strong>₹<?= $totalTransactions > 0 ? number_format((int)($totalGatewayRevenue / $totalTransactions)) : 0 ?></strong>
                <small>Average ticket size</small>
            </div>
            <div class="stat-card">
                <span>Payment Gateway</span>
                <strong>Razorpay</strong>
                <small>Webhooks & direct verification</small>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="dash-toolbar mt-4">
            <div class="dash-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="paymentSearch" placeholder="Search by student, payment ID, or workshop..." onkeyup="filterPayments()">
            </div>
            <div class="dash-filters">
                <button type="button" class="filter-pill active" onclick="setPayFilter('all', this)">All (<?= $totalTransactions ?>)</button>
                <button type="button" class="filter-pill" onclick="setPayFilter('paid', this)">Paid</button>
                <button type="button" class="filter-pill" onclick="setPayFilter('pending', this)">Pending</button>
                <button type="button" class="filter-pill" onclick="setPayFilter('refunded', this)">Refunded</button>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="admin-card p-0 mt-3">
            <div class="table-responsive">
                <table class="table bolso-table align-middle mb-0" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Txn ID</th>
                            <th>Student</th>
                            <th>Workshop & Mode</th>
                            <th>Provider</th>
                            <th>Provider Payment ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="8" class="empty-row">No payment records found yet. Payments processed via Razorpay or marked paid will show here.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $pay): ?>
                        <tr class="payment-row"
                            data-status="<?= htmlspecialchars($pay['status'], ENT_QUOTES, 'UTF-8') ?>"
                            data-text="<?= strtolower(htmlspecialchars(($pay['student_name'] ?? '') . ' ' . ($pay['provider_payment_id'] ?? '') . ' ' . ($pay['workshop'] ?? '') . ' ' . ($pay['provider'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>">
                            <td>#<?= (int)$pay['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($pay['student_name'] ?? 'Walk-in Student', ENT_QUOTES, 'UTF-8') ?></strong>
                                <small><?= htmlspecialchars($pay['student_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($pay['workshop'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                <small class="text-uppercase"><?= htmlspecialchars($pay['mode'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                    <?= htmlspecialchars($pay['provider'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="rzp-id-badge" style="font-size: 11px;">
                                    <?= htmlspecialchars($pay['provider_payment_id'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><strong class="text-success">₹<?= number_format((float)$pay['amount']) ?></strong></td>
                            <td>
                                <span class="status-pill <?= htmlspecialchars($pay['status'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($pay['status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(date('d M Y, H:i', strtotime($pay['created_at'])), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
let currentPayFilter = 'all';

function setPayFilter(filter, el) {
    currentPayFilter = filter;
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    filterPayments();
}

function filterPayments() {
    const search = document.getElementById('paymentSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.payment-row');

    rows.forEach(row => {
        const text = row.getAttribute('data-text') || '';
        const status = row.getAttribute('data-status') || '';

        const matchesSearch = !search || text.includes(search);
        let matchesFilter = true;

        if (currentPayFilter === 'paid') matchesFilter = (status === 'paid');
        else if (currentPayFilter === 'pending') matchesFilter = (status === 'pending');
        else if (currentPayFilter === 'refunded') matchesFilter = (status === 'refunded');

        row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
