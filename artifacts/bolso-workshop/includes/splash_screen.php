<?php
declare(strict_types=1);
/**
 * BOLSO Artisan Welcome & Entrance Splash Screen
 * Inspired by handcrafted botanical fabric painting.
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
    
    <div class="splash-content">
        <!-- Central Animated Artwork Stage -->
        <div class="splash-art-stage">
            <!-- Radiating translucent watercolor pigment droplets in BOLSO's palette -->
            <div class="splash-radial-halo" aria-hidden="true">
                <!-- Outer & inner pigment droplets mimicking handcrafted watercolor swatches -->
                <span class="radial-drop drop-1" style="--rot: -8deg; --dist: 135px; --delay: 0.05s; --w: 19px; --h: 52px; --bg: rgba(122, 143, 123, 0.55);"></span>
                <span class="radial-drop drop-2" style="--rot: 24deg; --dist: 155px; --delay: 0.15s; --w: 16px; --h: 42px; --bg: rgba(194, 94, 62, 0.42);"></span>
                <span class="radial-drop drop-3" style="--rot: 52deg; --dist: 130px; --delay: 0.22s; --w: 15px; --h: 36px; --bg: rgba(212, 151, 59, 0.45);"></span>
                <span class="radial-drop drop-4" style="--rot: 82deg; --dist: 158px; --delay: 0.1s; --w: 22px; --h: 54px; --bg: rgba(180, 160, 140, 0.48);"></span>
                <span class="radial-drop drop-5" style="--rot: 112deg; --dist: 138px; --delay: 0.28s; --w: 16px; --h: 40px; --bg: rgba(122, 143, 123, 0.52);"></span>
                <span class="radial-drop drop-6" style="--rot: 140deg; --dist: 146px; --delay: 0.18s; --w: 18px; --h: 46px; --bg: rgba(212, 151, 59, 0.42);"></span>
                <span class="radial-drop drop-7" style="--rot: 172deg; --dist: 132px; --delay: 0.08s; --w: 20px; --h: 50px; --bg: rgba(194, 94, 62, 0.45);"></span>
                <span class="radial-drop drop-8" style="--rot: 204deg; --dist: 156px; --delay: 0.25s; --w: 17px; --h: 44px; --bg: rgba(180, 160, 140, 0.50);"></span>
                <span class="radial-drop drop-9" style="--rot: 232deg; --dist: 134px; --delay: 0.14s; --w: 15px; --h: 38px; --bg: rgba(122, 143, 123, 0.56);"></span>
                <span class="radial-drop drop-10" style="--rot: 262deg; --dist: 160px; --delay: 0.3s; --w: 22px; --h: 52px; --bg: rgba(194, 94, 62, 0.40);"></span>
                <span class="radial-drop drop-11" style="--rot: 294deg; --dist: 136px; --delay: 0.12s; --w: 16px; --h: 40px; --bg: rgba(212, 151, 59, 0.46);"></span>
                <span class="radial-drop drop-12" style="--rot: 326deg; --dist: 148px; --delay: 0.24s; --w: 18px; --h: 48px; --bg: rgba(90, 30, 48, 0.32);"></span>
                
                <!-- Inner soft accent flecks -->
                <span class="radial-drop drop-in-1" style="--rot: 10deg; --dist: 92px; --delay: 0.35s; --w: 10px; --h: 22px; --bg: rgba(212, 151, 59, 0.5);"></span>
                <span class="radial-drop drop-in-2" style="--rot: 100deg; --dist: 96px; --delay: 0.38s; --w: 11px; --h: 24px; --bg: rgba(122, 143, 123, 0.55);"></span>
                <span class="radial-drop drop-in-3" style="--rot: 190deg; --dist: 90px; --delay: 0.32s; --w: 10px; --h: 20px; --bg: rgba(194, 94, 62, 0.48);"></span>
                <span class="radial-drop drop-in-4" style="--rot: 280deg; --dist: 98px; --delay: 0.42s; --w: 12px; --h: 26px; --bg: rgba(90, 30, 48, 0.35);"></span>
            </div>

            <!-- Central Botanical Art Leaf SVG (drawing veins & luminous glint) -->
            <div class="splash-leaf-wrap">
                <svg class="splash-leaf-svg" viewBox="0 0 360 180" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Handcrafted botanical leaf">
                    <defs>
                        <!-- Botanical Leaf Pigment Gradient -->
                        <linearGradient id="bolsoLeafGrad" x1="25" y1="90" x2="335" y2="90" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#448d51"/>
                            <stop offset="35%" stop-color="#357a41"/>
                            <stop offset="70%" stop-color="#2a6634"/>
                            <stop offset="100%" stop-color="#1f4f27"/>
                        </linearGradient>

                        <!-- Top Sheen Gradient -->
                        <linearGradient id="bolsoLeafSheen" x1="180" y1="20" x2="180" y2="160" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.25"/>
                            <stop offset="45%" stop-color="#ffffff" stop-opacity="0.04"/>
                            <stop offset="100%" stop-color="#0a1f0d" stop-opacity="0.22"/>
                        </linearGradient>

                        <!-- Luminous Gleam Filter -->
                        <filter id="leafGlowFilter" x="-30%" y="-30%" width="160%" height="160%">
                            <feGaussianBlur in="SourceGraphic" stdDeviation="3.5" result="blur"/>
                            <feMerge>
                                <feMergeNode in="blur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>

                        <!-- Travelling Light Beam Gradient -->
                        <linearGradient id="veinLightBeam" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#ffffff" stop-opacity="0"/>
                            <stop offset="30%" stop-color="#ffffff" stop-opacity="0.2"/>
                            <stop offset="50%" stop-color="#ffffff" stop-opacity="0.95"/>
                            <stop offset="70%" stop-color="#ffffff" stop-opacity="0.2"/>
                            <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    <!-- Soft Ambient Drop Shadow -->
                    <path class="leaf-shadow" d="M 38 92 C 68 45, 152 22, 330 92 C 255 156, 105 150, 38 92 Z" fill="rgba(35, 52, 32, 0.22)" filter="url(#leafGlowFilter)"/>

                    <!-- Main Botanical Leaf Body -->
                    <path class="leaf-body" d="M 35 90 C 66 42, 150 20, 328 90 C 255 154, 102 148, 35 90 Z" fill="url(#bolsoLeafGrad)"/>
                    <path class="leaf-sheen" d="M 35 90 C 66 42, 150 20, 328 90 C 255 154, 102 148, 35 90 Z" fill="url(#bolsoLeafSheen)"/>

                    <!-- Branching White Veins (Smooth SVG Drawing Animation) -->
                    <!-- Upper Branching Veins -->
                    <path class="leaf-vein vein-up-1" d="M 102 83 C 114 62, 142 46, 178 38" stroke="#ffffff" stroke-width="2.4" stroke-linecap="round"/>
                    <path class="leaf-vein vein-up-2" d="M 158 84 C 180 64, 212 50, 246 45" stroke="#ffffff" stroke-width="2.3" stroke-linecap="round"/>
                    <path class="leaf-vein vein-up-3" d="M 220 86 C 242 70, 270 58, 298 57" stroke="#ffffff" stroke-width="2.0" stroke-linecap="round"/>

                    <!-- Lower Branching Veins -->
                    <path class="leaf-vein vein-down-1" d="M 80 92 C 98 114, 130 132, 168 138" stroke="#ffffff" stroke-width="2.4" stroke-linecap="round"/>
                    <path class="leaf-vein vein-down-2" d="M 140 91 C 162 114, 200 130, 235 131" stroke="#ffffff" stroke-width="2.3" stroke-linecap="round"/>
                    <path class="leaf-vein vein-down-3" d="M 208 90 C 230 106, 262 118, 288 118" stroke="#ffffff" stroke-width="2.0" stroke-linecap="round"/>

                    <!-- Central Rib / Spine (Dark Artisan Stroke, Drawing in) -->
                    <path class="leaf-spine-backdrop" d="M 36 91 C 110 88, 195 88, 328 91" stroke="rgba(10, 20, 12, 0.35)" stroke-width="4.8" stroke-linecap="round"/>
                    <path class="leaf-spine" d="M 35 90 C 110 87, 195 87, 328 90" stroke="#1c211d" stroke-width="3.6" stroke-linecap="round"/>

                    <!-- Luminous Light Beam sweeping along the spine -->
                    <ellipse class="spine-glint" cx="42" cy="89" rx="26" ry="6" fill="url(#veinLightBeam)" filter="url(#leafGlowFilter)"/>
                </svg>

                <!-- Floating Artisan Stars -->
                <span class="splash-sparkle sp-1"><i class="bi bi-stars"></i></span>
                <span class="splash-sparkle sp-2"><i class="bi bi-star-fill"></i></span>
            </div>
        </div>

        <!-- Studio Seal / Circular Medallion -->
        <div class="splash-brand-group">
            <div class="splash-medallion" title="BOLSO Fabric Art Studio Seal">
                <div class="medallion-stitch-ring"></div>
                <div class="medallion-inner">
                    <img src="assets/images/logo.png" alt="BOLSO Studio Seal" class="medallion-logo">
                </div>
            </div>

            <!-- Brand Typography -->
            <div class="splash-text-block">
                <h1 class="splash-brand-name">BOLSO</h1>
                <p class="splash-brand-tagline">
                    <span class="gem">✦</span>
                    <span class="txt">ART · EMOTION · FASHION</span>
                    <span class="gem">✦</span>
                </p>
                <p class="splash-sub-kicker">Small-Batch Fabric Painting Studio · Kolkata</p>
            </div>

            <!-- Interactive Enter Button & Countdown Ring -->
            <div class="splash-actions-wrap">
                <button type="button" class="btn-splash-enter" id="btnSplashEnter" aria-label="Enter BOLSO Studio">
                    <span class="enter-lbl">Enter Studio</span>
                    <i class="bi bi-arrow-right enter-ico"></i>
                    <svg class="splash-progress-ring" viewBox="0 0 40 40" aria-hidden="true">
                        <circle class="ring-track" cx="20" cy="20" r="17"/>
                        <circle class="ring-fill" id="splashRingFill" cx="20" cy="20" r="17"/>
                    </svg>
                </button>
            </div>

            <div class="splash-skip-wrap">
                <button type="button" class="splash-skip-link" id="btnSplashSkip">
                    <span>Skip to website <i class="bi bi-chevron-double-right"></i></span>
                </button>
            </div>
        </div>
    </div>
</div>
