<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Reserve your spot';
$activePage = 'registration';
$errors = [];
$success = null;
$pendingPayment = null;
$paymentSuccess = null;
$submitted = $_POST;

$workshop = $_POST['workshop'] ?? $_GET['workshop'] ?? '2-day';
$mode = $_POST['mode'] ?? 'online';
$prices = ['2-day_online' => 399, '2-day_offline' => 399, '5-day_online' => 899, '5-day_offline' => 1299];
$priceKey = $workshop . '_' . $mode;
$price = $prices[$priceKey] ?? 399;

// 1. Verify Razorpay Payment Signature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify_payment') {
    $regId = (int)($_POST['registration_id'] ?? 0);
    $razorpayPaymentId = trim((string)($_POST['razorpay_payment_id'] ?? ''));
    $razorpayOrderId = trim((string)($_POST['razorpay_order_id'] ?? ''));
    $razorpaySignature = trim((string)($_POST['razorpay_signature'] ?? ''));

    $keySecret = bolso_config('payment_secret');
    $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);

    $pdo = bolso_db();
    if ($pdo && $expectedSignature === $razorpaySignature && $razorpayPaymentId !== '') {
        try {
            // Update registration payment status to paid
            $stmt = $pdo->prepare('UPDATE registrations SET payment_status = "paid" WHERE id = :id');
            $stmt->execute([':id' => $regId]);

            // Fetch registration details
            $stmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $regId]);
            $registration = $stmt->fetch();

            if ($registration) {
                // Record in payments table
                try {
                    $payStmt = $pdo->prepare(
                        'INSERT INTO payments (registration_id, provider, provider_payment_id, amount, status)
                         VALUES (:reg_id, "razorpay", :pay_id, :amount, "paid")'
                    );
                    $payStmt->execute([
                        ':reg_id' => $regId,
                        ':pay_id' => $razorpayPaymentId,
                        ':amount' => $registration['price'],
                    ]);
                } catch (PDOException $e) {
                    error_log('Failed to record payment in payments table: ' . $e->getMessage());
                }

                require_once __DIR__ . '/includes/notifications.php';
                $notifResult = bolso_notify_payment_success($registration, $razorpayPaymentId);

                $paymentSuccess = [
                    'id' => $registration['id'],
                    'name' => $registration['name'],
                    'email' => $registration['email'],
                    'whatsapp' => $registration['whatsapp'],
                    'workshop' => $registration['workshop'],
                    'mode' => $registration['mode'],
                    'preferred_date' => $registration['preferred_date'],
                    'price' => (int)$registration['price'],
                    'payment_id' => $razorpayPaymentId,
                    'customer_wa_link' => $notifResult['customer_whatsapp_link'] ?? '',
                    'admin_wa_link' => $notifResult['admin_whatsapp_link'] ?? '',
                ];
            }
        } catch (PDOException $exception) {
            error_log('BOLSO payment update failed: ' . $exception->getMessage());
            $errors[] = 'Payment was successful, but we had trouble updating the status. Please contact us.';
        }
    } else {
        $errors[] = 'Payment signature verification failed. If money was deducted, please contact us.';
    }
}

// 2. Handle Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'verify_payment') {
    $name = trim((string)($_POST['name'] ?? ''));
    $whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $preferredDate = trim((string)($_POST['preferred_date'] ?? ''));
    $interest = trim((string)($_POST['interest'] ?? ''));
    $experience = trim((string)($_POST['experience'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($name === '') $errors[] = 'Please add your full name.';
    if ($whatsapp === '') $errors[] = 'Please add your WhatsApp number.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!in_array($workshop, ['2-day', '5-day'], true)) $errors[] = 'Please choose a valid workshop.';
    if (!in_array($mode, ['online', 'offline'], true)) $errors[] = 'Please choose a valid mode.';
    if ($preferredDate === '') $errors[] = 'Please share your preferred date or batch.';
    if ($interest === '') $errors[] = 'Please tell us what you want to paint.';

    if (!$errors) {
        $pdo = bolso_db();
        if (!$pdo) {
            $errors[] = 'Registration is temporarily unavailable. Please configure the MySQL connection in config/config.local.php and try again.';
        } else {
            try {
                $statement = $pdo->prepare(
                    'INSERT INTO registrations (name, whatsapp, email, workshop, mode, preferred_date, interest, experience, message, price, payment_status)
                     VALUES (:name, :whatsapp, :email, :workshop, :mode, :preferred_date, :interest, :experience, :message, :price, :payment_status)'
                );
                $statement->execute([
                    ':name' => $name,
                    ':whatsapp' => $whatsapp,
                    ':email' => $email,
                    ':workshop' => $workshop,
                    ':mode' => $mode,
                    ':preferred_date' => $preferredDate,
                    ':interest' => $interest,
                    ':experience' => $experience,
                    ':message' => $message,
                    ':price' => $price,
                    ':payment_status' => 'pending',
                ]);
                $registrationId = (int)$pdo->lastInsertId();

                // Dispatch instant registration notification to student and admin immediately upon form submit
                require_once __DIR__ . '/includes/notifications.php';
                $initialRegData = [
                    'id' => $registrationId,
                    'name' => $name,
                    'whatsapp' => $whatsapp,
                    'email' => $email,
                    'workshop' => $workshop,
                    'mode' => $mode,
                    'preferred_date' => $preferredDate,
                    'interest' => $interest,
                    'experience' => $experience,
                    'message' => $message,
                    'price' => $price,
                    'payment_status' => 'pending',
                    'payment_id' => 'INITIATED',
                ];
                bolso_notify_payment_success($initialRegData, 'REGISTRATION_SUBMITTED');

                $keyId = bolso_config('payment_key');
                $keySecret = bolso_config('payment_secret');

                if ($keyId && $keyId !== 'YOUR_PAYMENT_KEY') {
                    // Create Razorpay Order
                    $orderData = [
                        'amount' => $price * 100, // paise
                        'currency' => 'INR',
                        'receipt' => 'reg_' . $registrationId,
                        'notes' => [
                            'registration_id' => (string)$registrationId,
                            'name' => $name,
                            'workshop' => $workshop,
                        ],
                    ];

                    $ch = curl_init('https://api.razorpay.com/v1/orders');
                    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                    $rzpResponse = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    $orderResult = json_decode((string)$rzpResponse, true);

                    if ($httpCode === 200 && !empty($orderResult['id'])) {
                        $pendingPayment = [
                            'registration_id' => $registrationId,
                            'order_id' => $orderResult['id'],
                            'amount' => $orderResult['amount'],
                            'price' => $price,
                            'name' => $name,
                            'email' => $email,
                            'whatsapp' => $whatsapp,
                            'workshop' => $workshop,
                            'mode' => $mode,
                        ];
                    } else {
                        error_log('Razorpay Order creation failed: ' . $rzpResponse);
                        $errorDesc = $orderResult['error']['description'] ?? 'Payment gateway error';
                        $errors[] = 'Could not initiate Razorpay payment: ' . $errorDesc;
                    }
                } else {
                    require_once __DIR__ . '/includes/notifications.php';
                    $directReg = [
                        'id' => $registrationId,
                        'name' => $name,
                        'email' => $email,
                        'whatsapp' => $whatsapp,
                        'workshop' => $workshop,
                        'mode' => $mode,
                        'preferred_date' => $preferredDate,
                        'interest' => $interest,
                        'experience' => $experience,
                        'message' => $message,
                        'price' => $price,
                        'payment_id' => 'MANUAL_PENDING',
                    ];
                    $notifRes = bolso_notify_payment_success($directReg, 'DIRECT_BOOKING');

                    $success = [
                        'id' => $registrationId,
                        'name' => $name,
                        'email' => $email,
                        'whatsapp' => $whatsapp,
                        'workshop' => $workshop,
                        'mode' => $mode,
                        'preferred_date' => $preferredDate,
                        'price' => $price,
                        'customer_wa_link' => $notifRes['customer_whatsapp_link'] ?? '',
                        'admin_wa_link' => $notifRes['admin_whatsapp_link'] ?? '',
                    ];
                }
            } catch (PDOException $exception) {
                error_log('BOLSO registration insert failed: ' . $exception->getMessage());
                $errors[] = 'We could not save your registration right now. Please try again.';
            }
        }
    }
}

$whatsappMessage = '';
$whatsappPayload = $paymentSuccess ?? $success;
if ($whatsappPayload) {
    $statusText = $paymentSuccess ? 'Paid' : 'Pending';
    $whatsappMessage = rawurlencode(
        "Hello BOLSO! I registered for the {$whatsappPayload['workshop']} workshop.\n" .
        "Name: {$whatsappPayload['name']}\nMode: {$whatsappPayload['mode']}\nPreferred date: {$whatsappPayload['preferred_date']}\nPrice: ₹{$whatsappPayload['price']}\nPayment: {$statusText}"
    );
}

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-hero registration-hero">
        <div class="container page-hero-inner">
            <div class="section-label"><span>01</span><span class="line"></span><span>Save your place</span></div>
            <h1>Make room for<br><em>something new.</em></h1>
            <p>Tell us a little about yourself and the kind of fabric you’d like to bring to life.</p>
        </div>
    </section>
    <section class="registration-section section-space">
        <div class="container registration-grid">
            <div class="registration-form-wrap">
                <?php if ($paymentSuccess): ?>
                    <div class="success-panel reveal">
                        <span class="success-icon" style="color: #2e7d32;">✦</span>
                        <span class="eyebrow" style="color: #2e7d32;">Payment Received · Spot Confirmed</span>
                        <h2>Thank you,<br><em><?= htmlspecialchars($paymentSuccess['name'], ENT_QUOTES, 'UTF-8') ?>.</em></h2>
                        <p>We’ve received your payment of <strong>₹<?= number_format($paymentSuccess['price']) ?></strong> for the <strong><?= htmlspecialchars($paymentSuccess['workshop']) ?> workshop</strong>. Your spot is officially booked!</p>

                        <!-- User Requested Clarification Banner on Timing -->
                        <div style="background: #fff8eb; border: 1px solid #ebd4b5; border-left: 5px solid #c97a3e; padding: 18px 22px; border-radius: 12px; margin: 24px 0; text-align: left;">
                            <h4 style="margin: 0 0 8px 0; color: #8e232e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                🕒 Workshop Timing &amp; Schedule
                            </h4>
                            <p style="margin: 0; font-size: 14.5px; line-height: 1.55; color: #2e3842;">
                                <strong>Your timing for the workshop will be given to you very soon!</strong><br>
                                A confirmation and thank-you message has been sent to your email (<strong><?= htmlspecialchars($paymentSuccess['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>) and WhatsApp. Our studio admin has also received your booking details and will coordinate your batch schedule shortly.
                            </p>
                        </div>

                        <p class="config-note" style="font-family: monospace; font-size: 13px;">Payment ID: <?= htmlspecialchars($paymentSuccess['payment_id'], ENT_QUOTES, 'UTF-8') ?></p>
                        
                        <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                            <?php if (!empty($paymentSuccess['customer_wa_link'])): ?>
                                <a class="btn btn-primary-bolso" href="<?= htmlspecialchars($paymentSuccess['customer_wa_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="background: #25d366; border-color: #25d366; color: #fff;">
                                    <i class="bi bi-whatsapp"></i> WhatsApp Confirmation <i class="bi bi-arrow-up-right"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($paymentSuccess['admin_wa_link'])): ?>
                                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($paymentSuccess['admin_wa_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="font-size: 13px;">
                                    <i class="bi bi-bell"></i> Alert Admin on WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 pt-3 border-top" style="font-size: 12px; color: #6a7c92;">
                            <span><i class="bi bi-check-circle-fill text-success"></i> Notifications dispatched to Admin (<strong>10abhishekkr@gmail.com</strong> &amp; <strong>WhatsApp 9341469219</strong>)</span>
                        </div>

                        <a class="text-link dark-link d-block mt-4" href="index.php">Back to BOLSO <i class="bi bi-arrow-right"></i></a>
                    </div>
                <?php elseif ($pendingPayment): ?>
                    <div class="success-panel reveal">
                        <span class="success-icon">✦</span>
                        <span class="eyebrow">Registration Saved</span>
                        <h2>Complete payment to confirm,<br><em><?= htmlspecialchars($pendingPayment['name'], ENT_QUOTES, 'UTF-8') ?>.</em></h2>
                        <p>Your details for the <strong><?= htmlspecialchars($pendingPayment['workshop']) ?> workshop</strong> (<?= htmlspecialchars($pendingPayment['mode']) ?>) are saved. Please pay <strong>₹<?= number_format($pendingPayment['price']) ?></strong> to secure your spot.</p>
                        <div class="mt-4 mb-3">
                            <button id="rzp-pay-button" class="btn btn-primary-bolso" style="font-size: 16px; padding: 14px 28px; cursor: pointer;">
                                Pay ₹<?= number_format($pendingPayment['price']) ?> with UPI / Card <i class="bi bi-arrow-up-right"></i>
                            </button>
                        </div>
                        <p class="config-note">The Razorpay payment window will open automatically. If it doesn’t, click the button above.</p>
                    </div>

                    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                    <script>
                    var options = {
                        "key": "<?= htmlspecialchars(bolso_config('payment_key'), ENT_QUOTES, 'UTF-8') ?>",
                        "amount": "<?= (int)$pendingPayment['amount'] ?>",
                        "currency": "INR",
                        "name": "<?= htmlspecialchars(BOLSO_NAME, ENT_QUOTES, 'UTF-8') ?>",
                        "description": "<?= htmlspecialchars($pendingPayment['workshop'] . ' Workshop Registration', ENT_QUOTES, 'UTF-8') ?>",
                        "order_id": "<?= htmlspecialchars($pendingPayment['order_id'], ENT_QUOTES, 'UTF-8') ?>",
                        "handler": function (response){
                            var form = document.createElement('form');
                            form.method = 'POST';
                            form.action = 'registration.php';

                            var fields = {
                                'action': 'verify_payment',
                                'registration_id': '<?= (int)$pendingPayment['registration_id'] ?>',
                                'razorpay_payment_id': response.razorpay_payment_id,
                                'razorpay_order_id': response.razorpay_order_id,
                                'razorpay_signature': response.razorpay_signature
                            };

                            for (var key in fields) {
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = fields[key];
                                form.appendChild(input);
                            }

                            document.body.appendChild(form);
                            form.submit();
                        },
                        "prefill": {
                            "name": <?= json_encode($pendingPayment['name']) ?>,
                            "email": <?= json_encode($pendingPayment['email']) ?>,
                            "contact": <?= json_encode($pendingPayment['whatsapp']) ?>
                        },
                        "theme": {
                            "color": "#7c2639"
                        }
                    };

                    var rzp = new Razorpay(options);

                    document.getElementById('rzp-pay-button').onclick = function(e){
                        rzp.open();
                        e.preventDefault();
                    };

                    window.addEventListener('load', function() {
                        setTimeout(function() {
                            rzp.open();
                        }, 400);
                    });
                    </script>
                <?php elseif ($success): ?>
                    <div class="success-panel reveal">
                        <span class="success-icon" style="color: #2e7d32;">✦</span>
                        <span class="eyebrow" style="color: #2e7d32;">Registration Saved · Spot Confirmed</span>
                        <h2>Thank you,<br><em><?= htmlspecialchars($success['name'], ENT_QUOTES, 'UTF-8') ?>.</em></h2>
                        <p>Your BOLSO workshop registration is saved and confirmed! We are delighted to have you join us.</p>

                        <!-- User Requested Clarification Banner on Timing -->
                        <div style="background: #fff8eb; border: 1px solid #ebd4b5; border-left: 5px solid #c97a3e; padding: 18px 22px; border-radius: 12px; margin: 24px 0; text-align: left;">
                            <h4 style="margin: 0 0 8px 0; color: #8e232e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                🕒 Workshop Timing &amp; Batch Schedule
                            </h4>
                            <p style="margin: 0; font-size: 14.5px; line-height: 1.55; color: #2e3842;">
                                <strong>Your timing for the workshop will be given to you very soon!</strong><br>
                                A confirmation and thank-you message has been sent to your email (<strong><?= htmlspecialchars($success['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>) and WhatsApp. Our studio admin has been alerted and will share your exact schedule shortly.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                            <?php if (!empty($success['customer_wa_link'])): ?>
                                <a class="btn btn-primary-bolso" href="<?= htmlspecialchars($success['customer_wa_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="background: #25d366; border-color: #25d366; color: #fff;">
                                    <i class="bi bi-whatsapp"></i> WhatsApp Confirmation <i class="bi bi-arrow-up-right"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($success['admin_wa_link'])): ?>
                                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($success['admin_wa_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="font-size: 13px;">
                                    <i class="bi bi-bell"></i> Alert Admin on WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 pt-3 border-top" style="font-size: 12px; color: #6a7c92;">
                            <span><i class="bi bi-check-circle-fill text-success"></i> Studio Admin alerted at <strong>10abhishekkr@gmail.com</strong> &amp; <strong>WhatsApp (+91 9341469219)</strong></span>
                        </div>

                        <a class="text-link dark-link d-block mt-4" href="index.php">Back to BOLSO <i class="bi bi-arrow-right"></i></a>
                    </div>
                <?php else: ?>
                    <?php if ($errors): ?><div class="alert bolso-alert" role="alert"><strong>Almost there.</strong><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                    <form method="post" action="registration.php" class="registration-form" novalidate>
                        <div class="form-step"><span>01</span><h2>Your details</h2></div>
                        <div class="row g-4">
                            <div class="col-md-6 form-field"><label for="name">Full name <span>*</span></label><input id="name" name="name" value="<?= htmlspecialchars((string)($submitted['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Your name" required></div>
                            <div class="col-md-6 form-field"><label for="whatsapp">WhatsApp number <span>*</span></label><input id="whatsapp" name="whatsapp" value="<?= htmlspecialchars((string)($submitted['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="+91 00000 00000" required></div>
                            <div class="col-12 form-field"><label for="email">Email address <span>*</span></label><input type="email" id="email" name="email" value="<?= htmlspecialchars((string)($submitted['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="you@example.com" required></div>
                        </div>
                        <div class="form-step"><span>02</span><h2>Choose your workshop</h2></div>
                        <div class="row g-4">
                            <div class="col-md-6 form-field"><label for="workshop">Workshop <span>*</span></label><select id="workshop" name="workshop"><option value="2-day" <?= $workshop === '2-day' ? 'selected' : '' ?>>2-Day Workshop</option><option value="5-day" <?= $workshop === '5-day' ? 'selected' : '' ?>>5-Day Workshop</option></select></div>
                            <div class="col-md-6 form-field"><label for="mode">Mode <span>*</span></label><select id="mode" name="mode"><option value="online" <?= $mode === 'online' ? 'selected' : '' ?>>Online · Live Google Meet</option><option value="offline" <?= $mode === 'offline' ? 'selected' : '' ?>>Offline · In person</option></select></div>
                            <div class="col-12 price-display"><span>Your workshop investment</span><strong id="priceDisplay">₹<?= number_format($price) ?></strong><small id="priceNote"><?= $mode === 'offline' && $workshop === '5-day' ? 'Materials and colours provided.' : 'Final details shared after registration.' ?></small></div>
                            <div class="col-12 form-field"><label for="preferred_date">Preferred date / batch <span>*</span></label><input id="preferred_date" name="preferred_date" value="<?= htmlspecialchars((string)($submitted['preferred_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. First weekend of September" required></div>
                        </div>
                        <div class="form-step"><span>03</span><h2>Make it yours</h2></div>
                        <div class="form-field"><label>What would you like to paint? <span>*</span></label><div class="interest-options"><label><input type="radio" name="interest" value="T-shirt" <?= ($submitted['interest'] ?? '') === 'T-shirt' ? 'checked' : '' ?>><span>T-shirt</span></label><label><input type="radio" name="interest" value="Bag" <?= ($submitted['interest'] ?? '') === 'Bag' ? 'checked' : '' ?>><span>Bag</span></label><label><input type="radio" name="interest" value="Jeans/Denim" <?= ($submitted['interest'] ?? '') === 'Jeans/Denim' ? 'checked' : '' ?>><span>Jeans / Denim</span></label><label><input type="radio" name="interest" value="Other Fabric" <?= ($submitted['interest'] ?? '') === 'Other Fabric' ? 'checked' : '' ?>><span>Other fabric</span></label></div></div>
                        <div class="form-field"><label for="experience">Previous experience</label><select id="experience" name="experience"><option value="">Choose one</option><option <?= ($submitted['experience'] ?? '') === 'None yet' ? 'selected' : '' ?>>None yet</option><option <?= ($submitted['experience'] ?? '') === 'A little' ? 'selected' : '' ?>>A little</option><option <?= ($submitted['experience'] ?? '') === 'I paint regularly' ? 'selected' : '' ?>>I paint regularly</option></select></div>
                        <div class="form-field"><label for="message">Anything else you’d like us to know?</label><textarea id="message" name="message" rows="4" placeholder="Tell us what you’re imagining..."><?= htmlspecialchars((string)($submitted['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        <button class="btn btn-primary-bolso submit-btn" type="submit">Send my registration <i class="bi bi-arrow-up-right"></i></button>
                    </form>
                <?php endif; ?>
            </div>
            <aside class="registration-aside">
                <div class="aside-sticky">
                    <span class="eyebrow">A small note</span>
                    <h3>Come as you are.</h3>
                    <p>You don’t need a portfolio, a perfect idea or a “creative” background. Just bring a fabric piece you care about and the willingness to play.</p>
                    <div class="aside-rule"></div>
                    <span class="eyebrow">Questions?</span>
                    <p class="mb-2">We’re happy to help you choose a workshop or mode.</p>
                    <a class="text-link dark-link" href="https://wa.me/919674773475" target="_blank" rel="noopener">Talk to BOLSO <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </aside>
        </div>
    </section>
</main>
<script>
window.bolsoPrices = <?= json_encode($prices, JSON_THROW_ON_ERROR) ?>;
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>