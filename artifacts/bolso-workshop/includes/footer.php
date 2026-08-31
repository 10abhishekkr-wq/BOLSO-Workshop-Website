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

<!-- BOLSO LOCATION START -->
<section id="location" class="location-section" style="padding:40px 0;">
  <div style="max-width:1100px;margin:0 auto;padding:0 20px;">
    <h2 style="height=30px;width=40px;">📍 Find Us</h2>
    <p>Jayanti Abasan, Jhowtala Hatiara, Near Lokenath Mandir, Chinar Park, Kolkata - 700157</p>
    <p><a href="https://www.google.com/maps/search/?api=1&query=22.620363,88.440207" target="_blank" rel="noopener noreferrer">Open exact location in Google Maps</a></p>
    <div style="width:100%;height:420px;border-radius:12px;overflow:hidden;">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d768.0613064844823!2d88.44032207163403!3d22.620195114226544!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39f89f0005d7015d%3A0x50925ce8d30dd50f!2sBOLSO!5e0!3m2!1sen!2sin!4v1787937473038!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
  </div>
</section>
<!-- BOLSO LOCATION END -->

</footer>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $isAdminArea ? '../assets/js/main.js' : 'assets/js/main.js' ?>"></script>
</body>
</html>


 