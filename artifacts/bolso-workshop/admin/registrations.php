<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_admin();

$pdo = bolso_db();
$error = null;
$registrations = [];
$workshops = [];

if ($pdo) {
    try {
        // Fetch active workshops for dropdowns
        $workshops = $pdo->query('SELECT slug, title, online_price, offline_price FROM workshops WHERE active = 1 ORDER BY duration_days ASC')->fetchAll();

        // 1. Handle CSV Export
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=bolso_registrations_' . date('Y-m-d') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'WhatsApp', 'Email', 'Workshop', 'Mode', 'Preferred Date', 'Canvas Interest', 'Experience', 'Student Notes', 'Price (INR)', 'Payment Status', 'Provider Payment ID', 'Registered At']);
            $rows = $pdo->query(
                'SELECT r.*, p.provider_payment_id 
                 FROM registrations r 
                 LEFT JOIN payments p ON p.registration_id = r.id 
                 ORDER BY r.registration_date DESC'
            )->fetchAll();
            foreach ($rows as $r) {
                fputcsv($output, [
                    $r['id'],
                    $r['name'],
                    $r['whatsapp'],
                    $r['email'],
                    $r['workshop'],
                    $r['mode'],
                    $r['preferred_date'],
                    $r['interest'],
                    $r['experience'] ?? '',
                    $r['message'] ?? '',
                    $r['price'],
                    $r['payment_status'],
                    $r['provider_payment_id'] ?? '',
                    $r['registration_date'],
                ]);
            }
            fclose($output);
            exit;
        }

        // 2. Handle POST Actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_admin_csrf($_POST['csrf_token'] ?? null)) {
                $error = 'Session security token expired. Please refresh and try again.';
            } else {
                $action = (string)($_POST['action'] ?? '');

                // A. Update Payment Status
                if ($action === 'update_payment') {
                    $regId = filter_var($_POST['registration_id'] ?? null, FILTER_VALIDATE_INT);
                    $status = (string)($_POST['payment_status'] ?? '');
                    if ($regId && in_array($status, ['pending', 'paid', 'failed', 'refunded'], true)) {
                        $stmt = $pdo->prepare('UPDATE registrations SET payment_status = :status WHERE id = :id');
                        $stmt->execute([':status' => $status, ':id' => $regId]);
                        
                        if ($status === 'paid') {
                            $rStmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id LIMIT 1');
                            $rStmt->execute([':id' => $regId]);
                            $regRow = $rStmt->fetch(PDO::FETCH_ASSOC);
                            if ($regRow) {
                                bolso_notify_payment_success($regRow, 'ADMIN_CONFIRMED');
                            }
                            admin_flash('success', "Payment marked as PAID. Confirmation email & WhatsApp alert sent to student and admin (10abhishekkr@gmail.com / 9341469219).");
                        } else {
                            admin_flash('success', "Payment status updated to " . strtoupper($status) . ".");
                        }
                        header('Location: registrations.php');
                        exit;
                    } else {
                        $error = 'Invalid registration ID or payment status.';
                    }
                }

                // B. Add New Manual Registration (Walk-in / WhatsApp booking)
                elseif ($action === 'add_registration') {
                    $name = trim((string)($_POST['name'] ?? ''));
                    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
                    $email = trim((string)($_POST['email'] ?? ''));
                    $workshopSlug = trim((string)($_POST['workshop'] ?? '2-day'));
                    $mode = trim((string)($_POST['mode'] ?? 'offline'));
                    $preferredDate = trim((string)($_POST['preferred_date'] ?? ''));
                    $interest = trim((string)($_POST['interest'] ?? 'Tote bag'));
                    $experience = trim((string)($_POST['experience'] ?? 'Beginner'));
                    $message = trim((string)($_POST['message'] ?? 'Direct manual booking by studio admin'));
                    $price = (float)($_POST['price'] ?? 399);
                    $payStatus = trim((string)($_POST['payment_status'] ?? 'paid'));

                    if ($name === '' || $whatsapp === '') {
                        $error = 'Please provide student name and WhatsApp number.';
                    } else {
                        $insertStmt = $pdo->prepare(
                            'INSERT INTO registrations (name, whatsapp, email, workshop, mode, preferred_date, interest, experience, message, price, payment_status)
                             VALUES (:name, :whatsapp, :email, :workshop, :mode, :preferred_date, :interest, :experience, :message, :price, :pay_status)'
                        );
                        $insertStmt->execute([
                            ':name' => $name,
                            ':whatsapp' => $whatsapp,
                            ':email' => $email ?: 'walkin@bolso.art',
                            ':workshop' => $workshopSlug,
                            ':mode' => $mode,
                            ':preferred_date' => $preferredDate ?: date('d M Y'),
                            ':interest' => $interest,
                            ':experience' => $experience,
                            ':message' => $message,
                            ':price' => $price,
                            ':pay_status' => $payStatus,
                        ]);
                        $newId = (int)$pdo->lastInsertId();

                        // If marked as paid, record in payments table and dispatch notifications
                        if ($payStatus === 'paid') {
                            $payStmt = $pdo->prepare(
                                'INSERT INTO payments (registration_id, provider, provider_payment_id, amount, status)
                                 VALUES (:reg_id, "manual_admin", :pay_id, :amount, "paid")'
                            );
                            $payStmt->execute([
                                ':reg_id' => $newId,
                                ':pay_id' => 'MANUAL_' . time(),
                                ':amount' => $price,
                            ]);

                            $rStmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id LIMIT 1');
                            $rStmt->execute([':id' => $newId]);
                            $newRegRow = $rStmt->fetch(PDO::FETCH_ASSOC);
                            if ($newRegRow) {
                                bolso_notify_payment_success($newRegRow, 'ADMIN_MANUAL_WALKIN');
                            }
                        }

                        admin_flash('success', "Student {$name} successfully registered! Confirmation sent to student and admin.");
                        header('Location: registrations.php');
                        exit;
                    }
                }

                // C. Send Workshop Timing to Student
                elseif ($action === 'send_timing') {
                    $regId = filter_var($_POST['registration_id'] ?? null, FILTER_VALIDATE_INT);
                    $timingSlot = trim((string)($_POST['timing_slot'] ?? ''));
                    $venueOrLink = trim((string)($_POST['venue_or_link'] ?? ''));
                    $notes = trim((string)($_POST['notes'] ?? ''));

                    if (!$regId || $timingSlot === '') {
                        $error = 'Please provide the workshop timing details.';
                    } else {
                        $res = bolso_notify_timing_schedule($regId, $timingSlot, $venueOrLink, $notes);
                        if (!empty($res['success'])) {
                            admin_flash('success', "Workshop timing dispatched to student via Email and WhatsApp! Admin confirmation copy logged.");
                        } else {
                            $error = 'Failed to dispatch timing: ' . ($res['error'] ?? 'Unknown error');
                        }
                        header('Location: registrations.php');
                        exit;
                    }
                }

                // D. Resend Payment Confirmation & Timing Reassurance
                elseif ($action === 'resend_confirmation') {
                    $regId = filter_var($_POST['registration_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($regId) {
                        bolso_resend_payment_confirmation($regId);
                        admin_flash('success', "Payment receipt & timing clarification resent to student and admin (10abhishekkr@gmail.com / 9341469219).");
                        header('Location: registrations.php');
                        exit;
                    }
                }

                // C. Edit Existing Student Details
                elseif ($action === 'edit_registration') {
                    $regId = filter_var($_POST['registration_id'] ?? null, FILTER_VALIDATE_INT);
                    $name = trim((string)($_POST['name'] ?? ''));
                    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
                    $email = trim((string)($_POST['email'] ?? ''));
                    $workshopSlug = trim((string)($_POST['workshop'] ?? '2-day'));
                    $mode = trim((string)($_POST['mode'] ?? 'online'));
                    $preferredDate = trim((string)($_POST['preferred_date'] ?? ''));
                    $interest = trim((string)($_POST['interest'] ?? ''));
                    $experience = trim((string)($_POST['experience'] ?? ''));
                    $price = (float)($_POST['price'] ?? 0);

                    if (!$regId || $name === '' || $whatsapp === '') {
                        $error = 'Invalid data submitted for student update.';
                    } else {
                        $upStmt = $pdo->prepare(
                            'UPDATE registrations 
                             SET name = :name, whatsapp = :whatsapp, email = :email, workshop = :workshop,
                                 mode = :mode, preferred_date = :preferred_date, interest = :interest,
                                 experience = :experience, price = :price
                             WHERE id = :id'
                        );
                        $upStmt->execute([
                            ':name' => $name,
                            ':whatsapp' => $whatsapp,
                            ':email' => $email,
                            ':workshop' => $workshopSlug,
                            ':mode' => $mode,
                            ':preferred_date' => $preferredDate,
                            ':interest' => $interest,
                            ':experience' => $experience,
                            ':price' => $price,
                            ':id' => $regId,
                        ]);
                        admin_flash('success', "Registration for {$name} updated.");
                        header('Location: registrations.php');
                        exit;
                    }
                }

                // D. Delete Registration
                elseif ($action === 'delete_registration') {
                    $regId = filter_var($_POST['registration_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($regId) {
                        $stmt = $pdo->prepare('DELETE FROM registrations WHERE id = :id');
                        $stmt->execute([':id' => $regId]);
                        admin_flash('success', 'Registration removed.');
                        header('Location: registrations.php');
                        exit;
                    }
                }
            }
        }

        // Fetch all registrations with payment provider ID
        $registrations = $pdo->query(
            'SELECT r.*, p.provider_payment_id 
             FROM registrations r 
             LEFT JOIN payments p ON p.registration_id = r.id 
             ORDER BY r.registration_date DESC'
        )->fetchAll();

    } catch (PDOException $e) {
        error_log('Registrations page error: ' . $e->getMessage());
        $error = 'Could not load registrations list.';
    }
}

$pageTitle = 'Manage Registrations';
$isAdminArea = true;
$adminActiveTab = 'registrations';
require __DIR__ . '/../includes/header.php';
?>

<main class="admin-page-container">
    <div class="container-fluid px-lg-4">
        <!-- Header & Action Toolbar -->
        <div class="dashboard-heading">
            <div>
                <span class="eyebrow">BOLSO Studio / Enrolments</span>
                <h1>Student Registrations</h1>
            </div>
            <div class="dash-actions">
                <button type="button" class="btn btn-primary-bolso" data-bs-toggle="modal" data-bs-target="#newStudentModal">
                    <i class="bi bi-person-plus"></i> New Student
                </button>
                <a href="registrations.php?export=csv" class="btn btn-outline-bolso" title="Export as CSV spreadsheet">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
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

        <!-- Search & Filter Controls -->
        <div class="dash-toolbar mt-4">
            <div class="dash-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="studentSearch" placeholder="Search by name, email, WhatsApp, or workshop..." onkeyup="filterStudents()">
            </div>
            <div class="dash-filters">
                <button type="button" class="filter-pill active" onclick="setFilter('all', this)">All (<?= count($registrations) ?>)</button>
                <button type="button" class="filter-pill" onclick="setFilter('paid', this)">Paid</button>
                <button type="button" class="filter-pill" onclick="setFilter('pending', this)">Pending</button>
                <button type="button" class="filter-pill" onclick="setFilter('online', this)">Online</button>
                <button type="button" class="filter-pill" onclick="setFilter('offline', this)">Studio (Offline)</button>
            </div>
        </div>

        <!-- Student Table -->
        <div class="admin-card p-0 mt-3">
            <div class="table-responsive">
                <table class="table bolso-table align-middle mb-0" id="registrationsTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Workshop</th>
                            <th>Mode</th>
                            <th>Date / Batch</th>
                            <th>Price</th>
                            <th>Payment</th>
                            <th>Timing & Schedule</th>
                            <th>Registered</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr><td colspan="9" class="empty-row">No registrations found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($registrations as $r): 
                        $cleanPhone = preg_replace('/\D/', '', $r['whatsapp']);
                        $waText = rawurlencode("Hello {$r['name']}! Greetings from BOLSO studio. Regarding your {$r['workshop']} registration...");
                    ?>
                        <tr class="student-row"
                            data-mode="<?= htmlspecialchars($r['mode'], ENT_QUOTES, 'UTF-8') ?>"
                            data-status="<?= htmlspecialchars($r['payment_status'], ENT_QUOTES, 'UTF-8') ?>"
                            data-text="<?= strtolower(htmlspecialchars($r['name'] . ' ' . $r['email'] . ' ' . $r['whatsapp'] . ' ' . $r['workshop'] . ' ' . $r['preferred_date'], ENT_QUOTES, 'UTF-8')) ?>">
                            <td>
                                <strong>
                                    <?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?>
                                    <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waText ?>" target="_blank" rel="noopener" class="wa-link" title="Open WhatsApp chat">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </strong>
                                <small>
                                    <a href="mailto:<?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?>" class="text-secondary"><?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?></a> &bull; <?= htmlspecialchars($r['whatsapp'], ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['workshop'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <small>Canvas: <?= htmlspecialchars($r['interest'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td>
                                <span class="mode-pill <?= $r['mode'] === 'offline' ? 'offline' : 'online' ?>">
                                    <?= htmlspecialchars($r['mode'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($r['preferred_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong>₹<?= number_format((int)$r['price']) ?></strong></td>
                            <td>
                                <span class="status-pill <?= htmlspecialchars($r['payment_status'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($r['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <?php if (!empty($r['provider_payment_id'])): ?>
                                    <div class="rzp-id-badge" title="Razorpay ID">
                                        <?= htmlspecialchars($r['provider_payment_id'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($r['timing_slot'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 px-2 py-1 mb-1" style="font-size: 11px;">
                                        <i class="bi bi-clock-check"></i> <?= htmlspecialchars($r['timing_slot'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <button type="button" class="btn btn-link btn-xs p-0 d-block text-muted text-decoration-none" style="font-size: 10px;"
                                            onclick='openTimingModal(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil-square"></i> Change timing
                                    </button>
                                <?php elseif ($r['payment_status'] === 'paid'): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle d-inline-flex align-items-center gap-1 px-2 py-1 mb-1" style="font-size: 11px;">
                                        <i class="bi bi-hourglass-split"></i> Timing Pending
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 d-block" style="font-size: 11px;"
                                            onclick='openTimingModal(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-send me-1"></i> Send Timing
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px;">
                                        Awaiting Payment
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars(date('d M Y', strtotime($r['registration_date'])), ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td class="text-end pe-4">
                                <div class="manage-actions justify-content-end">
                                    <!-- Quick Payment Status Toggle -->
                                    <form method="post" class="payment-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="update_payment">
                                        <input type="hidden" name="registration_id" value="<?= (int)$r['id'] ?>">
                                        <select name="payment_status" onchange="this.form.submit()" aria-label="Update status">
                                            <option value="pending" <?= $r['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="paid" <?= $r['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                            <option value="failed" <?= $r['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                                            <option value="refunded" <?= $r['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                        </select>
                                    </form>

                                    <!-- Send Timing Schedule Button -->
                                    <button type="button" class="btn btn-sm btn-outline-success p-1" title="Send Workshop Timing (Email & WhatsApp)"
                                            onclick='openTimingModal(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-clock-history"></i>
                                    </button>

                                    <!-- Resend Payment Confirmation & Timing Reassurance -->
                                    <form method="post" class="d-inline" onsubmit="return confirm('Resend payment confirmation and timing reassurance to student & admin?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="resend_confirmation">
                                        <input type="hidden" name="registration_id" value="<?= (int)$r['id'] ?>">
                                        <button class="btn btn-sm btn-outline-info p-1" type="submit" title="Resend Payment Confirmation & Timing Clarification"><i class="bi bi-envelope-arrow-up"></i></button>
                                    </form>

                                    <!-- View Details Button -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" title="View Full Details"
                                            onclick='openDetailsModal(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" title="Edit Student"
                                            onclick='openEditModal(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="post" onsubmit="return confirm('Permanently delete registration for <?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?>?');" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete_registration">
                                        <input type="hidden" name="registration_id" value="<?= (int)$r['id'] ?>">
                                        <button class="delete-button" type="submit" title="Delete"><i class="bi bi-trash3"></i></button>
                                    </form>
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

<!-- 1. MODAL: Add Walk-in Student -->
<div class="modal fade bolso-modal" id="newStudentModal" tabindex="-1" aria-labelledby="newStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="add_registration">
                
                <div class="modal-header">
                    <div>
                        <span class="eyebrow">Manual Booking</span>
                        <h5 class="modal-title" id="newStudentModalLabel">Register Walk-in / Phone Student</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="add_name">Full Name *</label>
                            <input type="text" class="form-control" id="add_name" name="name" required placeholder="e.g. Priya Roy">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_whatsapp">WhatsApp Number *</label>
                            <input type="text" class="form-control" id="add_whatsapp" name="whatsapp" required placeholder="e.g. 919876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_email">Email Address</label>
                            <input type="email" class="form-control" id="add_email" name="email" placeholder="priya@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_preferred_date">Batch Date / Preferred Date *</label>
                            <input type="text" class="form-control" id="add_preferred_date" name="preferred_date" required placeholder="e.g. 12-13 Sept Batch">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_workshop">Workshop *</label>
                            <select class="form-select" id="add_workshop" name="workshop" onchange="autoUpdatePrice()">
                                <?php foreach ($workshops as $ws): ?>
                                    <option value="<?= htmlspecialchars($ws['slug'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-online="<?= (int)$ws['online_price'] ?>" 
                                            data-offline="<?= (int)$ws['offline_price'] ?>">
                                        <?= htmlspecialchars($ws['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_mode">Mode *</label>
                            <select class="form-select" id="add_mode" name="mode" onchange="autoUpdatePrice()">
                                <option value="offline">Offline (Studio)</option>
                                <option value="online">Online (Live Meet)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="add_price">Amount (₹) *</label>
                            <input type="number" class="form-control" id="add_price" name="price" value="399" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_interest">Canvas of Interest</label>
                            <select class="form-select" id="add_interest" name="interest">
                                <option value="Tote bag">Tote bag</option>
                                <option value="Scarf">Scarf</option>
                                <option value="Cushion cover">Cushion cover</option>
                                <option value="Fabric sampler">Fabric sampler</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="add_pay_status">Payment Status *</label>
                            <select class="form-select" id="add_pay_status" name="payment_status">
                                <option value="paid">Paid (Cash / Direct Transfer)</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="add_message">Notes / Message</label>
                            <textarea class="form-control" id="add_message" name="message" rows="2" placeholder="Special requests or offline booking notes..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-bolso">Save Registration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. MODAL: View Details -->
<div class="modal fade bolso-modal" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="eyebrow">Enrolment Profile</span>
                    <h5 class="modal-title" id="detail_name">Student Details</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-grid-item">
                        <small>WhatsApp Phone</small>
                        <strong id="detail_phone">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Email</small>
                        <strong id="detail_email">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Workshop</small>
                        <strong id="detail_workshop">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Mode</small>
                        <strong id="detail_mode">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Preferred Batch Date</small>
                        <strong id="detail_date">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Canvas Selected</small>
                        <strong id="detail_interest">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Experience Level</small>
                        <strong id="detail_experience">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Investment</small>
                        <strong id="detail_price">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Payment Status</small>
                        <strong id="detail_status">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Razorpay Payment ID</small>
                        <strong id="detail_rzp">-</strong>
                    </div>
                    <div class="detail-grid-item">
                        <small>Workshop Timing & Schedule</small>
                        <strong id="detail_timing" class="text-success">-</strong>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Student Message / Notes</small>
                    <p id="detail_message" class="p-2 mt-1 border rounded bg-light small mb-0">-</p>
                </div>
            </div>
            <div class="modal-footer">
                <a id="detail_wa_btn" href="#" target="_blank" class="btn btn-outline-success">
                    <i class="bi bi-whatsapp"></i> Chat on WhatsApp
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. MODAL: Edit Registration -->
<div class="modal fade bolso-modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="edit_registration">
                <input type="hidden" name="registration_id" id="edit_reg_id">
                
                <div class="modal-header">
                    <div>
                        <span class="eyebrow">Modify Enrolment</span>
                        <h5 class="modal-title" id="editModalLabel">Edit Student Registration</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_name">Full Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_whatsapp">WhatsApp *</label>
                            <input type="text" class="form-control" id="edit_whatsapp" name="whatsapp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_date">Preferred Date</label>
                            <input type="text" class="form-control" id="edit_date" name="preferred_date">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_workshop">Workshop</label>
                            <select class="form-select" id="edit_workshop" name="workshop">
                                <?php foreach ($workshops as $ws): ?>
                                    <option value="<?= htmlspecialchars($ws['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($ws['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_mode">Mode</label>
                            <select class="form-select" id="edit_mode" name="mode">
                                <option value="online">Online</option>
                                <option value="offline">Offline</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="edit_price">Price (₹)</label>
                            <input type="number" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_interest">Canvas Interest</label>
                            <input type="text" class="form-control" id="edit_interest" name="interest">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_experience">Experience</label>
                            <input type="text" class="form-control" id="edit_experience" name="experience">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-bolso">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. MODAL: Send Workshop Timing -->
<div class="modal fade bolso-modal" id="timingModal" tabindex="-1" aria-labelledby="timingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="send_timing">
                <input type="hidden" name="registration_id" id="timing_reg_id">

                <div class="modal-header">
                    <div>
                        <span class="eyebrow text-success">Schedule Dispatch</span>
                        <h5 class="modal-title" id="timingModalLabel">Send Workshop Timing to Student</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3 small d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                        <div>
                            Dispatches official timing email and WhatsApp message to <strong id="timing_student_name">Student</strong>. A confirmation copy will be sent to admin (<span class="text-dark">10abhishekkr@gmail.com</span>).
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="timing_slot_input"><strong>Workshop Timing & Schedule *</strong></label>
                        <input type="text" class="form-control" id="timing_slot_input" name="timing_slot" required placeholder="e.g. Saturday, 12 Sept 2026 • 10:30 AM to 1:30 PM">
                        <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                            <span class="text-muted small me-1">Quick presets:</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:11px;" onclick="setPresetTiming('Saturday & Sunday • 11:00 AM – 1:30 PM')">Weekend Morning</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:11px;" onclick="setPresetTiming('Saturday & Sunday • 3:00 PM – 5:30 PM')">Weekend Afternoon</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:11px;" onclick="setPresetTiming('Upcoming Saturday • 10:00 AM – 1:00 PM')">Sat 10 AM – 1 PM</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="timing_venue_input">Location / Session Link (Optional)</label>
                        <input type="text" class="form-control" id="timing_venue_input" name="venue_or_link" placeholder="Google Meet link or Studio Address">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="timing_notes_input">Preparation Notes / Material Checklist</label>
                        <textarea class="form-control" id="timing_notes_input" name="notes" rows="2" placeholder="e.g. Please keep fabric canvas, paints, brushes, and water cup ready."></textarea>
                    </div>

                    <div id="timing_direct_wa_wrap" class="p-3 bg-light rounded border" style="display:none;">
                        <span class="small text-muted d-block mb-1">Direct Student WhatsApp link:</span>
                        <a id="timing_direct_wa_btn" href="#" target="_blank" class="btn btn-sm btn-success w-100">
                            <i class="bi bi-whatsapp me-1"></i> Open & Preview in WhatsApp Web
                        </a>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-bolso">
                        <i class="bi bi-send me-1"></i> Dispatch Schedule Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentFilter = 'all';

function setFilter(filter, el) {
    currentFilter = filter;
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    filterStudents();
}

function filterStudents() {
    const search = document.getElementById('studentSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.student-row');

    rows.forEach(row => {
        const text = row.getAttribute('data-text') || '';
        const mode = row.getAttribute('data-mode') || '';
        const status = row.getAttribute('data-status') || '';

        const matchesSearch = !search || text.includes(search);
        let matchesFilter = true;

        if (currentFilter === 'paid') matchesFilter = (status === 'paid');
        else if (currentFilter === 'pending') matchesFilter = (status === 'pending');
        else if (currentFilter === 'online') matchesFilter = (mode === 'online');
        else if (currentFilter === 'offline') matchesFilter = (mode === 'offline');

        row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

function autoUpdatePrice() {
    const wsSelect = document.getElementById('add_workshop');
    const modeSelect = document.getElementById('add_mode');
    const priceInput = document.getElementById('add_price');
    const selectedOption = wsSelect.options[wsSelect.selectedIndex];
    if (selectedOption) {
        const isOffline = modeSelect.value === 'offline';
        const price = isOffline ? selectedOption.getAttribute('data-offline') : selectedOption.getAttribute('data-online');
        if (price) priceInput.value = price;
    }
}

function openDetailsModal(r) {
    document.getElementById('detail_name').innerText = r.name || 'Student';
    document.getElementById('detail_phone').innerText = r.whatsapp || '-';
    document.getElementById('detail_email').innerText = r.email || '-';
    document.getElementById('detail_workshop').innerText = r.workshop || '-';
    document.getElementById('detail_mode').innerText = (r.mode || '').toUpperCase();
    document.getElementById('detail_date').innerText = r.preferred_date || '-';
    document.getElementById('detail_interest').innerText = r.interest || '-';
    document.getElementById('detail_experience').innerText = r.experience || 'Not specified';
    document.getElementById('detail_price').innerText = '₹' + Number(r.price).toLocaleString();
    document.getElementById('detail_status').innerText = (r.payment_status || '').toUpperCase();
    document.getElementById('detail_rzp').innerText = r.provider_payment_id || 'None';
    document.getElementById('detail_timing').innerText = r.timing_slot ? (r.timing_slot + (r.timing_sent_at ? ' (Dispatched: ' + r.timing_sent_at + ')' : '')) : 'Timing not yet assigned (Pending)';
    document.getElementById('detail_message').innerText = r.message || 'No notes provided.';

    const clean = (r.whatsapp || '').replace(/\D/g, '');
    const waUrl = 'https://wa.me/' + clean + '?text=' + encodeURIComponent('Hello ' + r.name + '! Greetings from BOLSO studio.');
    document.getElementById('detail_wa_btn').href = waUrl;

    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();
}

function openTimingModal(r) {
    document.getElementById('timing_reg_id').value = r.id;
    document.getElementById('timing_student_name').innerText = (r.name || 'Student') + ' (' + (r.whatsapp || '') + ')';
    document.getElementById('timing_slot_input').value = r.timing_slot || '';
    if (r.mode === 'offline') {
        document.getElementById('timing_venue_input').value = 'BOLSO Art Studio, Indiranagar, Bengaluru';
    } else {
        document.getElementById('timing_venue_input').value = 'Google Meet: https://meet.google.com/bolso-art-session';
    }
    document.getElementById('timing_notes_input').value = 'Please keep your fabric canvas, acrylic/fabric paints, brushes (#2 round, #8 flat), water cup, and rag ready.';

    const clean = (r.whatsapp || '').replace(/\D/g, '');
    if (clean) {
        const waText = encodeURIComponent('Hello ' + r.name + '! Your BOLSO workshop timing has been confirmed: ' + (r.timing_slot || 'Upcoming batch') + '. See you soon!');
        document.getElementById('timing_direct_wa_btn').href = 'https://wa.me/' + clean + '?text=' + waText;
        document.getElementById('timing_direct_wa_wrap').style.display = 'block';
    } else {
        document.getElementById('timing_direct_wa_wrap').style.display = 'none';
    }

    const modal = new bootstrap.Modal(document.getElementById('timingModal'));
    modal.show();
}

function setPresetTiming(txt) {
    document.getElementById('timing_slot_input').value = txt;
}

function openEditModal(r) {
    document.getElementById('edit_reg_id').value = r.id;
    document.getElementById('edit_name').value = r.name || '';
    document.getElementById('edit_whatsapp').value = r.whatsapp || '';
    document.getElementById('edit_email').value = r.email || '';
    document.getElementById('edit_date').value = r.preferred_date || '';
    document.getElementById('edit_workshop').value = r.workshop || '2-day';
    document.getElementById('edit_mode').value = r.mode || 'online';
    document.getElementById('edit_price').value = r.price || 399;
    document.getElementById('edit_interest').value = r.interest || '';
    document.getElementById('edit_experience').value = r.experience || '';

    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

// Auto open modal if URL has ?action=new
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'new') {
        const modal = new bootstrap.Modal(document.getElementById('newStudentModal'));
        modal.show();
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
