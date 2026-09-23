<?php
declare(strict_types=1);
/**
 * BOLSO Artisan Welcome & Entrance Splash Screen
 * Focused, centered luxury studio branding.
 */
$isStandalone = $isStandalone ?? false;
?>
<div id="bolsoSplashScreen" class="bolso-splash-overlay <?= $isStandalone ? 'standalone-mode' : '' ?>" aria-label="BOLSO Welcome Animation" role="dialog" aria-modal="true">
    <div class="splash-backdrop">
        <div class="splash-linen-texture" aria-hidden="true"></div>
        <div class="splash-ambient-glow" aria-hidden="true"></div>
        <!-- Ambient floating dust particles -->
        <div class="splash-dust-layer" aria-hidden="true">
            <span class="dust-dot d1"></span>
            <span class="dust-dot d2"></span>
            <span class="dust-dot d3"></span>
            <span class="dust-dot d4"></span>
            <span class="dust-dot d5"></span>
            <span class="dust-dot d6"></span>
        </div>
    </div>
    
    <div class="splash-content splash-centered-content">
        <!-- Studio Seal / Circular Medallion (Large Centered Luxury Disc) -->
        <div class="splash-brand-group">
            <div class="splash-medallion splash-medallion-lg" title="BOLSO Fabric Art Studio Seal">
                <div class="medallion-stitch-ring"></div>
                <div class="medallion-inner">
                    <img src="assets/images/logo.png?v=<?= file_exists(__DIR__ . '/../assets/images/logo.png') ? filemtime(__DIR__ . '/../assets/images/logo.png') : '3' ?>" alt="BOLSO Studio Seal" class="medallion-logo medallion-logo-lg">
                </div>
                <!-- Sparkle accents around the medallion -->
                <span class="medallion-sparkle sp-top"><i class="bi bi-stars"></i></span>
                <span class="medallion-sparkle sp-bot"><i class="bi bi-star-fill"></i></span>
            </div>

            <!-- Brand Typography (Enlarged & Centered) -->
            <div class="splash-text-block">
                <h1 class="splash-brand-name splash-brand-name-lg">BOLSO</h1>
                <p class="splash-brand-tagline splash-brand-tagline-lg">
                    <span class="gem">✦</span>
                    <span class="txt">ART · EMOTION · FASHION</span>
                    <span class="gem">✦</span>
                </p>
                <p class="splash-sub-kicker splash-sub-kicker-lg">Small-Batch Fabric Painting Studio · Kolkata</p>
            </div>

            <!-- Interactive Enter Button & Circular Progress Ring -->
            <div class="splash-actions-wrap">
                <a href="index.php?enter=1" class="btn-splash-enter btn-splash-enter-lg text-decoration-none" id="btnSplashEnter" aria-label="Enter BOLSO Studio">
                    <span class="enter-lbl">Enter Studio</span>
                    <i class="bi bi-arrow-right enter-ico"></i>
                    <svg class="splash-progress-ring splash-progress-ring-lg" viewBox="0 0 40 40" aria-hidden="true">
                        <circle class="ring-track" cx="20" cy="20" r="17"/>
                        <circle class="ring-fill" id="splashRingFill" cx="20" cy="20" r="17"/>
                    </svg>
                </a>
            </div>

            <div class="splash-skip-wrap">
                <a href="index.php?enter=1" class="splash-skip-link text-decoration-none" id="btnSplashSkip">
                    <span>Skip to website <i class="bi bi-chevron-double-right"></i></span>
                </a>
                <span style="color: rgba(255,255,255,0.35); margin: 0 8px;">·</span>
                <a href="admin/index.php" class="splash-skip-link text-decoration-none" title="BOLSO Studio Admin">
                    <i class="bi bi-shield-lock me-1"></i><span>Admin Panel</span>
                </a>
            </div>
        </div>
    </div>
</div>
