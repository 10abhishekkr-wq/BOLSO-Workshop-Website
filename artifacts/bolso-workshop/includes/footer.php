<?php
declare(strict_types=1);
?>
<?php if (!$isAdminArea): ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand-col">
                <a class="brand-lockup footer-brand" href="index.php" aria-label="BOLSO home">
                    <img src="assets/images/logo_white.png" alt="BOLSO" class="brand-logo-img footer-logo-img" width="130" height="34" style="height: 34px; width: auto; max-width: 140px; display: inline-block; vertical-align: middle;">
                    <span class="brand-tagline footer-tagline">Art · Emotion · Fashion</span>
                </a>
                <p class="footer-note">Wear your imagination.<br>Make something that feels like you with handcrafted fabric art.</p>
                <div class="mt-3">
                    <a href="welcome.php" id="btnReplayIntro" class="badge rounded-pill bg-white bg-opacity-10 text-warning px-3 py-2 text-decoration-none" style="font-size: 11px; border: 1px solid rgba(229,169,60,0.3);">
                        <i class="bi bi-stars"></i> Replay Studio Intro
                    </a>
                </div>
            </div>

            <div class="footer-links">
                <span class="eyebrow">Explore</span>
                <a href="index.php">Home</a>
                <a href="workshops.php">Workshops &amp; Classes</a>
                <a href="registration.php">Reserve a Spot</a>
                <a href="index.php#about">About the Studio</a>
                <?php if (is_user_logged_in()): ?>
                    <a href="my-workshops.php">My Enrolled Workshops</a>
                <?php else: ?>
                    <a href="login.php">Student Sign In</a>
                <?php endif; ?>
                <a href="admin/index.php"><i class="bi bi-shield-lock me-1"></i> Admin Panel</a>
            </div>

            <div class="footer-links">
                <span class="eyebrow">Studio Concierge</span>
                <a href="https://wa.me/919341469219" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp (+91 9341469219)
                </a>
                <a href="mailto:10abhishekkr@gmail.com">
                    <i class="bi bi-envelope me-1"></i> 10abhishekkr@gmail.com
                </a>
                <div class="mt-2 pt-2 border-top border-white border-opacity-10">
                    <a href="admin/index.php" class="badge rounded-pill bg-white bg-opacity-10 text-white px-3 py-2 text-decoration-none d-inline-flex align-items-center gap-1 admin-footer-badge" style="border: 1px solid rgba(255,255,255,0.22); font-size: 11.5px; transition: all 0.2s ease;">
                        <i class="bi bi-shield-lock-fill text-warning"></i>
                        <span>Admin Studio Portal</span>
                    </a>
                </div>
            </div>

            <div class="footer-location-col">
                <span class="eyebrow">Studio Location</span>
                <p class="text-white-50 small mb-2" style="line-height: 1.5;">
                    <strong class="text-white">BOLSO Fabric Art Studio</strong><br>
                    Jayanti Abasan, Jhowtala Hatiara,<br>
                    Near Lokenath Mandir, Chinar Park,<br>
                    Kolkata - 700157
                </p>
                <a href="https://www.google.com/maps/search/?api=1&query=22.620363,88.440207" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-light mt-1" style="font-size: 11px;">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Open Google Maps
                </a>
            </div>
        </div>

        <!-- LOCATION MAP FRAME -->
        <div class="footer-map-container mt-4 pt-4 border-top border-secondary border-opacity-25">
            <div style="width: 100%; height: 260px; border-radius: 14px; overflow: hidden; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                <iframe style="width: 100%; height: 100%; border: 0;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d768.0613064844823!2d88.44032207163403!3d22.620195114226544!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39f89f0005d7015d%3A0x50925ce8d30dd50f!2sBOLSO!5e0!3m2!1sen!2sin!4v1787937473038!5m2!1sen!2sin" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© <?= date('Y') ?> BOLSO Fabric Art Studio. Made by hand.</span>
            <span>Small batches. Big feelings. Kolkata &amp; Worldwide. · <a href="admin/index.php" class="text-white-50 text-decoration-none" style="font-size: 11px;" title="BOLSO Studio Admin"><i class="bi bi-shield-lock me-1"></i>Admin Panel</a></span>
        </div>
    </div>
</footer>

<!-- Floating 1-Click WhatsApp Studio Concierge -->
<aside class="floating-whatsapp-concierge" id="whatsappConcierge" aria-label="WhatsApp Studio Concierge">
    <a href="https://wa.me/919341469219?text=Hi%20Abhishek!%20I%20have%20a%20query%20about%20the%20upcoming%20BOLSO%20fabric%20art%20workshop." 
       target="_blank" 
       rel="noopener noreferrer" 
       class="concierge-link" 
       title="Chat directly with Instructor Abhishek on WhatsApp">
        <div class="concierge-avatar-wrap">
            <i class="bi bi-whatsapp"></i>
            <span class="concierge-status-beacon" title="Abhishek is online"></span>
        </div>
        <div class="concierge-text">
            <span class="concierge-label">Have questions?</span>
            <strong class="concierge-action">Chat on WhatsApp</strong>
        </div>
        <div class="concierge-arrow">
            <i class="bi bi-arrow-up-right"></i>
        </div>
    </a>
</aside>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ($isAdminArea ? '../assets/js/main.js?v=' : 'assets/js/main.js?v=') . (file_exists(__DIR__ . '/../assets/js/main.js') ? (string)filemtime(__DIR__ . '/../assets/js/main.js') : '2.3') ?>"></script>
</body>
</html>
