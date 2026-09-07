<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_user();

$currentUser = current_user();
$userId = $currentUser['id'] ?? 0;
$userEmail = $currentUser['email'] ?? '';

$pdo = bolso_db();
$userRecord = null;
$registrations = [];

if ($pdo) {
    // 1. Fetch fresh profile data
    $userStmt = $pdo->prepare('SELECT id, name, email, whatsapp, created_at FROM users WHERE id = :id LIMIT 1');
    $userStmt->execute([':id' => $userId]);
    $userRecord = $userStmt->fetch();

    // 2. Fetch registrations for this user (by user_id OR email fallback)
    $regStmt = $pdo->prepare(
        'SELECT r.*, w.title AS workshop_title, w.duration_days,
                (SELECT provider_payment_id FROM payments WHERE registration_id = r.id ORDER BY id DESC LIMIT 1) AS provider_payment_id
         FROM registrations r
         LEFT JOIN workshops w ON w.slug = r.workshop
         WHERE r.user_id = :uid OR (r.user_id IS NULL AND LOWER(r.email) = LOWER(:email))
         ORDER BY r.id DESC'
    );
    $regStmt->execute([
        ':uid' => $userId,
        ':email' => $userEmail,
    ]);
    $registrations = $regStmt->fetchAll();
}

$flash = take_user_flash();
$displayName = $userRecord['name'] ?? $currentUser['name'] ?? 'Student';
$displayEmail = $userRecord['email'] ?? $currentUser['email'] ?? '';
$displayWhatsapp = $userRecord['whatsapp'] ?? $currentUser['whatsapp'] ?? '';
$joinedDate = !empty($userRecord['created_at']) ? date('M Y', strtotime($userRecord['created_at'])) : date('M Y');

$pageTitle = 'My Workshops';
$activePage = 'my-workshops';
require __DIR__ . '/includes/header.php';
?>
<main class="student-dashboard section-space">
    <div class="container">
        <!-- Flash messages -->
        <?php if ($flash): ?>
            <div class="alert <?= $flash['type'] === 'success' ? 'bolso-success-alert' : 'bolso-alert' ?> mb-4 alert-dismissible fade show" role="alert">
                <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill' ?> me-2"></i>
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Student Profile Overview Bar -->
        <div class="student-profile-bar mb-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="student-avatar" aria-hidden="true">
                            <?= htmlspecialchars(strtoupper(substr($displayName, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div>
                            <span class="eyebrow mb-1">Student Account</span>
                            <h1 class="student-name mb-1">Welcome, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></h1>
                            <div class="student-meta d-flex flex-wrap gap-3">
                                <span><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($displayEmail, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($displayWhatsapp !== ''): ?>
                                    <span><i class="bi bi-whatsapp me-1"></i>+<?= htmlspecialchars($displayWhatsapp, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <span><i class="bi bi-calendar3 me-1"></i>Member since <?= htmlspecialchars($joinedDate, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="d-inline-flex flex-wrap gap-2">
                        <a href="registration.php" class="btn btn-primary-bolso">
                            <i class="bi bi-plus-lg me-1"></i> Book a Workshop
                        </a>
                        <a href="logout.php" class="btn btn-outline-bolso" title="Sign out of student account">
                            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-section-header d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="eyebrow">Your Enrollments</span>
                <h2 class="section-title h3 mb-0">Enrolled Workshops (<?= count($registrations) ?>)</h2>
            </div>
            <?php if (!empty($registrations)): ?>
                <a href="workshops.php" class="text-link dark-link">
                    Explore all masterclasses <i class="bi bi-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($registrations)): ?>
            <!-- Empty State -->
            <div class="empty-dashboard-card text-center py-5 px-4 mb-5">
                <div class="empty-icon-wrap mb-3" aria-hidden="true">
                    <i class="bi bi-palette empty-icon"></i>
                </div>
                <h3 class="mb-2">No workshops booked yet</h3>
                <p class="text-muted mx-auto mb-4" style="max-width: 520px;">
                    Discover the art of fabric painting with BOLSO. Choose between our weekend 2-Day intensive or comprehensive 5-Day hands-on course, available both live online and at our Kolkata studio.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="workshops.php" class="btn btn-primary-bolso">
                        View Workshop Curriculum <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="registration.php" class="btn btn-outline-bolso">
                        Reserve Your Spot Now
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Workshop Bookings List -->
            <div class="row g-4 mb-5">
                <?php foreach ($registrations as $reg): ?>
                    <?php
                    $isOffline = ($reg['mode'] ?? '') === 'offline';
                    $isPayAtStudio = ($reg['payment_method'] ?? '') === 'offline';
                    $isPaid = ($reg['payment_status'] ?? '') === 'paid';
                    $workshopTitle = $reg['workshop_title'] ?? ($reg['workshop'] === '5-day' ? '5-Day Workshop' : '2-Day Workshop');
                    $bookingDate = date('d M Y', strtotime((string)$reg['registration_date']));
                    $regId = (int)$reg['id'];
                    $supportWaMsg = rawurlencode("Hi BOLSO! I'm " . $displayName . " (Registration #" . $regId . "). I have a question regarding my " . $workshopTitle . " enrollment.");
                    ?>
                    <div class="col-12 col-xl-6">
                        <div class="workshop-booking-card h-100">
                            <!-- Card Top Bar -->
                            <div class="booking-card-top d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="booking-id-tag">Registration #<?= $regId ?></span>
                                    <h3 class="booking-workshop-title mt-1 mb-0"><?= htmlspecialchars($workshopTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <?php if ($isPaid): ?>
                                        <span class="badge-payment-paid"><i class="bi bi-check-circle-fill me-1"></i> Paid · ₹<?= number_format((float)$reg['price'], 0) ?></span>
                                    <?php elseif ($isPayAtStudio): ?>
                                        <span class="badge-payment-studio"><i class="bi bi-clock-history me-1"></i> Spot Reserved · Pay ₹<?= number_format((float)$reg['price'], 0) ?> at Studio</span>
                                    <?php else: ?>
                                        <span class="badge-payment-pending"><i class="bi bi-hourglass-split me-1"></i> Payment Pending · ₹<?= number_format((float)$reg['price'], 0) ?></span>
                                    <?php endif; ?>

                                    <?php if ($isOffline): ?>
                                        <span class="badge-mode-offline"><i class="bi bi-geo-alt-fill me-1"></i> Studio · In Person</span>
                                    <?php else: ?>
                                        <span class="badge-mode-online"><i class="bi bi-camera-video-fill me-1"></i> Online · Google Meet</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Timing & Batch Information Banner -->
                            <div class="timing-highlight-box mb-3">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-calendar2-week timing-icon mt-1"></i>
                                    <div>
                                        <div class="timing-label">Preferred Batch / Date</div>
                                        <div class="timing-val"><?= htmlspecialchars($reg['preferred_date'] ?? 'To be scheduled', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if (!empty($reg['timing_slot'])): ?>
                                            <div class="timing-slot-confirmed mt-1">
                                                <i class="bi bi-clock me-1"></i> <strong>Assigned Slot:</strong> <?= htmlspecialchars($reg['timing_slot'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="timing-slot-notice mt-1">
                                                <i class="bi bi-info-circle me-1"></i> Exact batch time will be shared on WhatsApp 24-48 hours prior to Day 1.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Registration Details Breakdown -->
                            <div class="booking-details-grid mb-4">
                                <div class="booking-detail-item">
                                    <span class="detail-label">Item / Canvas</span>
                                    <span class="detail-val"><?= htmlspecialchars(ucfirst((string)($reg['interest'] ?? 'Tote Bag')), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="booking-detail-item">
                                    <span class="detail-label">Prior Experience</span>
                                    <span class="detail-val"><?= htmlspecialchars(ucfirst((string)($reg['experience'] ?? 'Beginner')), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="booking-detail-item">
                                    <span class="detail-label">Enrolled On</span>
                                    <span class="detail-val"><?= htmlspecialchars($bookingDate, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="booking-detail-item">
                                    <span class="detail-label">Student WhatsApp</span>
                                    <span class="detail-val"><?= htmlspecialchars((string)($reg['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>

                            <!-- Mode Specific Notice -->
                            <?php if ($isOffline): ?>
                                <div class="venue-info-box mb-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-pin-map-fill venue-icon mt-1"></i>
                                        <div>
                                            <strong class="d-block mb-1">Studio Venue Location:</strong>
                                            <p class="venue-addr mb-1">
                                                Jayanti Abasan, Jhowtala Hatiara, Near Lokenath Mandir, Chinar Park, Rajarhat, Kolkata - 700157
                                            </p>
                                            <small class="text-muted d-block">All premium fabric paints, brushes, canvases, and color palettes are provided at the studio.</small>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="venue-info-box mb-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-laptop venue-icon mt-1"></i>
                                        <div>
                                            <strong class="d-block mb-1">Online Live Class:</strong>
                                            <p class="venue-addr mb-1">
                                                Interactive live stream on Google Meet. The private meeting link and recommended starter material checklist are shared via WhatsApp and email before session commencement.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="booking-card-actions d-flex flex-wrap gap-2 pt-2 border-top">
                                <a href="https://wa.me/919674773475?text=<?= $supportWaMsg ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-bolso">
                                    <i class="bi bi-whatsapp text-success me-1"></i> Chat with Instructor
                                </a>
                                <?php if ($isOffline): ?>
                                    <a href="https://maps.google.com/?q=BOLSO+Art+Studio+Chinar+Park+Kolkata" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-bolso">
                                        <i class="bi bi-compass me-1"></i> Get Directions
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Need Help Concierge Card -->
        <div class="concierge-card p-4 p-md-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-md-8">
                    <span class="eyebrow mb-2">BOLSO Studio Concierge</span>
                    <h3 class="mb-2">Need to reschedule or have a question about materials?</h3>
                    <p class="text-muted mb-0">Our studio team is available on WhatsApp to assist with batch rescheduling, custom fabric requests, or offline studio travel assistance.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="https://wa.me/919674773475?text=Hi%20BOLSO%20Studio!%20I%20have%20a%20question%20regarding%20my%20workshop%20registration." target="_blank" rel="noopener noreferrer" class="btn btn-primary-bolso py-3 px-4">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp Studio
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
