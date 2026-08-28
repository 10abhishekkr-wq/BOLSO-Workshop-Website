<?php
declare(strict_types=1);
?>
<?php if (!$isAdminArea): ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div>
                <a class="brand-lockup footer-brand" href="index.php">
                    <span class="brand-mark">B</span>
                    <span><strong>BOLSO</strong><small>Art, Emotion, Fashion.</small></span>
                </a>
                <p class="footer-note">Wear your imagination.<br>Make something that feels like you.</p>
            </div>
            <div class="footer-links">
                <span class="eyebrow">Explore</span>
                <a href="workshops.php">Workshops</a>
                <a href="registration.php">Reserve a spot</a>
                <a href="index.php#about">About BOLSO</a>
            </div>
            <div class="footer-links">
                <span class="eyebrow">Say hello</span>
                <a href="https://wa.me/<?= urlencode(bolso_config('whatsapp')) ?>" target="_blank" rel="noopener">WhatsApp <i class="bi bi-arrow-up-right"></i></a>
                <a href="mailto:hello@bolso.art">hello@bolso.art</a>
                <span>Kolkata · India</span>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> BOLSO. Made by hand.</span>
            <span>Small batches. Big feelings.</span>
        </div>
    </div>
</footer>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $isAdminArea ? '../assets/js/main.js' : 'assets/js/main.js' ?>"></script>
</body>
</html>