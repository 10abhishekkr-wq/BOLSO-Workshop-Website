<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = 'Reserve your spot';
$activePage = 'registration';
$errors = [];
$success = null;
$submitted = $_POST;

$workshop = $_POST['workshop'] ?? $_GET['workshop'] ?? '2-day';
$mode = $_POST['mode'] ?? 'online';
$prices = ['2-day_online' => 399, '2-day_offline' => 399, '5-day_online' => 899, '5-day_offline' => 1299];
$priceKey = $workshop . '_' . $mode;
$price = $prices[$priceKey] ?? 399;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $success = [
                    'name' => $name,
                    'workshop' => $workshop,
                    'mode' => $mode,
                    'preferred_date' => $preferredDate,
                    'price' => $price,
                ];
            } catch (PDOException $exception) {
                error_log('BOLSO registration insert failed: ' . $exception->getMessage());
                $errors[] = 'We could not save your registration right now. Please try again.';
            }
        }
    }
}

$whatsappMessage = '';
if ($success) {
    $whatsappMessage = rawurlencode(
        "Hello BOLSO! I registered for the {$success['workshop']} workshop.\n" .
        "Name: {$success['name']}\nMode: {$success['mode']}\nPreferred date: {$success['preferred_date']}\nPrice: ₹{$success['price']}"
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
                <?php if ($success): ?>
                    <div class="success-panel reveal">
                        <span class="success-icon">✦</span>
                        <span class="eyebrow">You’re on the list</span>
                        <h2>Thank you,<br><em><?= htmlspecialchars($success['name'], ENT_QUOTES, 'UTF-8') ?>.</em></h2>
                        <p>Your BOLSO registration is saved. We’ll confirm your batch details shortly. You can also send the details directly on WhatsApp.</p>
                        <?php if (bolso_config('whatsapp') !== 'YOUR_WHATSAPP_NUMBER'): ?>
                            <a class="btn btn-primary-bolso" href="https://wa.me/919674773475" target="_blank" rel="noopener">Message on WhatsApp <i class="bi bi-arrow-up-right"></i></a>
                        <?php else: ?>
                            <p class="config-note">Add your WhatsApp number in <code>config/config.local.php</code> to enable the direct message button.</p>
                        <?php endif; ?>
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