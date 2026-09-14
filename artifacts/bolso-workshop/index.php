<?php
declare(strict_types=1);

$pageTitle = 'Learn to turn fabric into art';
$activePage = 'home';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero-section">
        <!-- Hanging artisan tag with animated string suspended from the top right corner -->
        <div class="hero-hanging-assembly" aria-label="Make it wearable craft tag">
            <div class="hanging-pin" title="Artisan Tag Pin"></div>
            <div class="hanging-string">
                <span class="string-cord"></span>
                <span class="string-knot"></span>
            </div>
            <div class="hero-stamp hanging-badge">
                <div class="stamp-eyelet"></div>
                <span>Make it<br><em>wearable</em></span>
                <i class="bi bi-stars"></i>
            </div>
        </div>

        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <div class="hero-kicker"><span></span> Small-batch fabric painting workshops</div>
                <h1>Learn to turn<br><em>fabric into art.</em></h1>
                <p class="hero-intro">Create beautiful, soft and wearable paintings with your own hands. BOLSO brings personal guidance, joyful practice and a little more colour to the everyday.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary-bolso hero-main-btn" href="registration.php">
                        <span>Reserve your spot</span> <i class="bi bi-arrow-up-right"></i>
                    </a>
                    <a class="hero-discover-btn" href="#about">
                        <span>Discover BOLSO</span> <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
                <div class="hero-proof">
                    <div class="proof-chip">
                        <i class="bi bi-people-fill text-terracotta"></i>
                        <strong>Only 6</strong> <span>students per batch</span>
                    </div>
                    <span class="proof-divider"></span>
                    <div class="proof-chip">
                        <i class="bi bi-calendar2-week-fill text-terracotta"></i>
                        <strong>2–5 days</strong> <span>of guided making</span>
                    </div>
                    <span class="proof-divider"></span>
                    <div class="proof-chip">
                        <i class="bi bi-star-fill text-warning"></i>
                        <strong>4.9/5</strong> <span>rated by 250+ makers</span>
                    </div>
                </div>
            </div>
            <div class="hero-art reveal reveal-delay-1">
                <div class="card-stack-container hero-stack-container" id="heroCardStackSlider" data-card-stack>
                    <div class="card-stack-track">
                        <div class="card-stack-item" data-index="0">
                            <img src="assets/images/slider/slide1.jpg" alt="Hand-painted custom T-shirts">
                            <div class="card-tag">01 · Hand-Painted Tees</div>
                        </div>
                        <div class="card-stack-item" data-index="1">
                            <img src="assets/images/slider/slide2.jpg" alt="Students painting at studio table">
                            <div class="card-tag">02 · Studio Workshop</div>
                        </div>
                        <div class="card-stack-item" data-index="2">
                            <img src="assets/images/slider/slide3.jpg" alt="Custom garment fabric painting">
                            <div class="card-tag">03 · Jacket Art</div>
                        </div>
                        <div class="card-stack-item" data-index="3">
                            <img src="assets/images/slider/slide4.jpg" alt="Botanical artwork painted on canvas tote bag">
                            <div class="card-tag">04 · Canvas Tote Art</div>
                        </div>
                        <div class="card-stack-item" data-index="4">
                            <img src="assets/images/slider/slide5.jpg" alt="Wearable denim floral painting">
                            <div class="card-tag">05 · Wearable Denim</div>
                        </div>
                        <div class="card-stack-item" data-index="5">
                            <img src="assets/images/slider/slide6.jpg" alt="Artisan textile pigment palettes">
                            <div class="card-tag">06 · Color Palette</div>
                        </div>
                    </div>

                    <!-- Navigation Controls & Indicators -->
                    <div class="card-stack-controls">
                        <button type="button" class="stack-arrow-btn stack-prev" aria-label="Previous artwork">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="stack-indicators">
                            <span class="stack-dot active" data-index="0"></span>
                            <span class="stack-dot" data-index="1"></span>
                            <span class="stack-dot" data-index="2"></span>
                            <span class="stack-dot" data-index="3"></span>
                            <span class="stack-dot" data-index="4"></span>
                            <span class="stack-dot" data-index="5"></span>
                        </div>
                        <button type="button" class="stack-arrow-btn stack-next" aria-label="Next artwork">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-bottom-line"><span>01</span><span class="line"></span><span>Art · Emotion · Fashion.</span></div>
    </section>

    <section class="marquee-strip" aria-label="BOLSO principles">
        <div class="marquee-track">
            <span>MAKE SOMETHING SOFT</span><i>✳</i>
            <span>MAKE SOMETHING YOU</span><i>✳</i>
            <span>MAKE SOMETHING TO WEAR</span><i>✳</i>
            <span>100% WASHABLE &amp; CRACK-FREE</span><i>✳</i>
            <span>HANDCRAFTED IN KOLKATA</span><i>✳</i>
            <span>SMALL-BATCH ARTISAN FABRIC ART</span><i>✳</i>
            <span>MAKE SOMETHING SOFT</span><i>✳</i>
        </div>
    </section>

    <section class="section-space about-section" id="about">
        <div class="container">
            <div class="section-label"><span>02</span><span class="line"></span><span>The BOLSO way</span></div>
            <div class="about-grid">
                <div class="about-heading reveal">
                    <div class="about-kicker"><span class="kicker-sparkle">✦</span><span>The Studio Philosophy</span></div>
                    <h2>There is art<br>in the <em class="brush-accent">everyday.</em></h2>
                    <div class="about-craft-card">
                        <div class="craft-card-badge">
                            <span class="craft-pill"><i class="bi bi-patch-check-fill"></i> Studio Standards</span>
                            <div class="craft-palette" title="Curated Studio Pigments">
                                <span class="palette-swatch swatch-maroon" title="Deep Maroon"></span>
                                <span class="palette-swatch swatch-rust" title="Terracotta"></span>
                                <span class="palette-swatch swatch-gold" title="Ochre Gold"></span>
                                <span class="palette-swatch swatch-sage" title="Sage Green"></span>
                                <span class="palette-swatch swatch-slate" title="Ink Navy"></span>
                            </div>
                        </div>
                        <p class="craft-quote">“We don’t paint on canvases that hang on walls. We paint on the clothes you live in.”</p>
                        <div class="craft-perks">
                            <div class="craft-perk">
                                <span class="craft-perk-icon"><i class="bi bi-droplet-half"></i></span>
                                <div class="craft-perk-body">
                                    <strong>Soft-Touch Inks</strong>
                                    <small>Zero crack · 100% washable</small>
                                </div>
                            </div>
                            <div class="craft-perk">
                                <span class="craft-perk-icon"><i class="bi bi-people-fill"></i></span>
                                <div class="craft-perk-body">
                                    <strong>Max 6 Students</strong>
                                    <small>Personal 1-on-1 guidance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-copy reveal reveal-delay-1">
                    <p class="lead-copy">A plain tee. A canvas tote. Your favourite old pair of jeans. We believe the things closest to us deserve a little more feeling.</p>
                    <p>BOLSO started with custom fabric paintings and grew into a place to learn the process for yourself. From the first brushstroke to the last soft wash, you’ll learn how to make colour sit beautifully on cloth.</p>
                    <a class="text-link dark-link" href="workshops.php">See what you’ll learn <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="material-row">
                <div class="material-card reveal">
                    <div class="material-card-media">
                        <img class="material-img" src="img3.png" alt="Hand-painted custom T-shirts">
                        <span class="material-tag">01 · Everyday Cotton</span>
                        <div class="material-overlay">
                            <span class="material-view-hint">Custom T-Shirts <i class="bi bi-arrow-up-right"></i></span>
                        </div>
                    </div>
                    <div class="material-card-body">
                        <div class="material-meta">
                            <span class="material-glyph">✦</span>
                            <span class="material-number">01 / Cotton</span>
                        </div>
                        <strong>Custom T-Shirts</strong>
                        <small>Turn everyday staples into personal, washable wearable art.</small>
                    </div>
                </div>
                <div class="material-card reveal reveal-delay-1">
                    <div class="material-card-media">
                        <img class="material-img" src="img4.png" alt="Hand-painted canvas tote bags">
                        <span class="material-tag">02 · Botanical Canvas</span>
                        <div class="material-overlay">
                            <span class="material-view-hint">Canvas Totes <i class="bi bi-arrow-up-right"></i></span>
                        </div>
                    </div>
                    <div class="material-card-body">
                        <div class="material-meta">
                            <span class="material-glyph">◌</span>
                            <span class="material-number">02 / Canvas</span>
                        </div>
                        <strong>Canvas Bags</strong>
                        <small>Heavyweight raw tote bags carrying joyful botanical illustrations.</small>
                    </div>
                </div>
                <div class="material-card reveal reveal-delay-2">
                    <div class="material-card-media">
                        <img class="material-img" src="img2.png" alt="Artisan painted denim jackets and jeans">
                        <span class="material-tag">03 · Upcycled Denim</span>
                        <div class="material-overlay">
                            <span class="material-view-hint">Denim Art <i class="bi bi-arrow-up-right"></i></span>
                        </div>
                    </div>
                    <div class="material-card-body">
                        <div class="material-meta">
                            <span class="material-glyph">⌁</span>
                            <span class="material-number">03 / Denim</span>
                        </div>
                        <strong>Denim &amp; Jackets</strong>
                        <small>Give old jeans and jackets a vivid second life with layered brushwork.</small>
                    </div>
                </div>
                <div class="material-card reveal reveal-delay-3">
                    <div class="material-card-media">
                        <img class="material-img" src="img1.png" alt="Linen and handcrafted fabric painting">
                        <span class="material-tag">04 · Open Canvases</span>
                        <div class="material-overlay">
                            <span class="material-view-hint">Fabric Art <i class="bi bi-arrow-up-right"></i></span>
                        </div>
                    </div>
                    <div class="material-card-body">
                        <div class="material-meta">
                            <span class="material-glyph">✺</span>
                            <span class="material-number">04 / Linen</span>
                        </div>
                        <strong>Linens &amp; Silks</strong>
                        <small>There are no wrong fabrics. Explore textures, silks, and home textiles.</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 03: BEFORE & AFTER TRANSFORMATION SLIDER -->
    <section class="transformation-section section-space" id="transformation">
        <div class="container">
            <div class="split-heading reveal">
                <div class="section-label"><span>03</span><span class="line"></span><span>The Artisan Transformation</span></div>
                <span class="artisan-badge-pill"><i class="bi bi-magic text-terracotta"></i> Interactive Reveal</span>
            </div>

            <div class="transformation-card reveal reveal-delay-1">
                <div class="row align-items-center g-4 g-xl-5">
                    <div class="col-lg-5">
                        <div class="transformation-content">
                            <span class="eyebrow">Real Student Outcome</span>
                            <h2>From plain cloth<br>to <em>wearable art.</em></h2>
                            <p class="transformation-desc">Drag the center artisan divider to reveal how an unpainted, store-bought cotton tote transforms into a permanent, machine-washable botanical artwork in just 2 days of guided making.</p>
                            
                            <div class="transformation-perks-list">
                                <div class="t-perk-item">
                                    <div class="t-perk-icon"><i class="bi bi-shield-check"></i></div>
                                    <div>
                                        <strong>100% Machine Washable</strong>
                                        <small>Heat-cured textile pigments that never crack, peel, or wash away.</small>
                                    </div>
                                </div>
                                <div class="t-perk-item">
                                    <div class="t-perk-icon"><i class="bi bi-hand-index-thumb"></i></div>
                                    <div>
                                        <strong>Ultra-Soft Cloth Touch</strong>
                                        <small>Soft-touch German pigments that sink deep into natural fibers with zero cloth stiffness.</small>
                                    </div>
                                </div>
                                <div class="t-perk-item">
                                    <div class="t-perk-icon"><i class="bi bi-palette2"></i></div>
                                    <div>
                                        <strong>Zero Art Background Required</strong>
                                        <small>Step-by-step tracing, outlining, and ombre wash techniques taught live.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-2">
                                <a href="registration.php" class="btn btn-primary-bolso">
                                    <span>Paint Your Own Bag</span> <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="comparison-outer-frame">
                            <div class="comparison-slider-widget" id="artisanComparisonSlider" data-position="50">
                                <!-- Base/Before Layer (Plain Cotton Bag) -->
                                <div class="comparison-layer comparison-layer-before">
                                    <img src="assets/images/canvas_tote_plain.jpg" alt="Before: Plain unpainted canvas tote bag" class="comparison-img" draggable="false">
                                    <span class="comparison-tag tag-before"><i class="bi bi-record-circle"></i> Before · Blank Cloth</span>
                                </div>

                                <!-- Overlay/After Layer (Hand-Painted Botanical Bag) -->
                                <div class="comparison-layer comparison-layer-after" id="comparisonOverlay">
                                    <img src="assets/images/canvas_tote_painted.png" alt="After: Handcrafted botanical painted tote bag" class="comparison-img" draggable="false">
                                    <span class="comparison-tag tag-after"><i class="bi bi-brush-fill"></i> After · Hand-Painted Art</span>
                                </div>

                                <!-- Tactile Artisan Slider Handle -->
                                <div class="comparison-slider-handle" id="comparisonHandle" role="slider" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" aria-label="Drag to compare before and after painting" tabindex="0">
                                    <div class="handle-stem-top"></div>
                                    <div class="handle-dial">
                                        <i class="bi bi-chevron-left"></i>
                                        <span class="handle-grip"></span>
                                        <i class="bi bi-chevron-right"></i>
                                    </div>
                                    <div class="handle-stem-bottom"></div>
                                </div>

                                <div class="slider-drag-hint">
                                    <i class="bi bi-arrows-expand-vertical"></i> <span>Drag left / right to reveal</span>
                                </div>
                            </div>

                            <div class="comparison-caption-bar">
                                <div class="caption-item">
                                    <span class="cap-step">Input</span>
                                    <strong class="cap-name">340 GSM Raw Cotton Tote</strong>
                                </div>
                                <div class="caption-arrow"><i class="bi bi-arrow-right"></i></div>
                                <div class="caption-item">
                                    <span class="cap-step">Output</span>
                                    <strong class="cap-name">Wearable Botanical Masterpiece</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="workshop-preview section-space">
        <div class="container">
            <div class="split-heading">
                <div class="section-label"><span>04</span><span class="line"></span><span>Choose your pace</span></div>
                <a class="text-link dark-link" href="workshops.php">View all workshops <i class="bi bi-arrow-up-right"></i></a>
            </div>
            <div class="workshop-preview-grid">
                <article class="workshop-tile tile-dark reveal">
                    <div class="tile-top"><span class="tile-tag">The quick start</span><span>01 / 02</span></div>
                    <div class="tile-middle">
                        <p class="tile-days">2 days · Fast-track</p>
                        <h3>Start with<br><em>the basics.</em></h3>
                        <p class="tile-desc">A hands-on introduction to fabric painting. Learn to turn plain cloth into soft, wearable art you'll love wearing.</p>
                        <div class="tile-learn-wrap">
                            <span class="tile-learn-title">What you'll learn:</span>
                            <ul class="tile-learn-points">
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>Fabric &amp; Pigment Basics:</strong> Prepping cotton, denim &amp; linen for smooth, crack-free colour absorption.</span></li>
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>Brushwork &amp; Shading:</strong> Fluid brush control, blending gradients and soft-wash layering.</span></li>
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>First Finished Garment:</strong> Paint and finish your own wearable custom T-shirt or canvas tote.</span></li>
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>Personal Mentorship:</strong> Live step-by-step guidance in intimate batches (max 6 students).</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="tile-bottom">
                        <div class="tile-price-box">
                            <div class="tile-price-main">
                                <span class="price-val">₹399</span>
                                <span class="price-unit">/ participant</span>
                            </div>
                            <span class="tile-mode-pill"><i class="bi bi-check2-circle"></i> Online &amp; Offline Available</span>
                        </div>
                        <a class="btn-tile-cta btn-tile-gold" href="registration.php?workshop=2-day">
                            <span>Book 2-Day Pass</span> <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </article>
                <article class="workshop-tile tile-sand reveal reveal-delay-1">
                    <div class="tile-badge-featured"><i class="bi bi-stars"></i> Studio Signature · Most Loved</div>
                    <div class="tile-top"><span class="tile-tag">The deep dive</span><span>02 / 02</span></div>
                    <div class="tile-middle">
                        <p class="tile-days">5 days · Complete mastery</p>
                        <h3>Find your<br><em>signature.</em></h3>
                        <p class="tile-desc">Immerse yourself in comprehensive fabric art. Build a distinct visual language and create a signature wearable collection.</p>
                        <div class="tile-learn-wrap">
                            <span class="tile-learn-title">What you'll learn:</span>
                            <ul class="tile-learn-points">
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>Advanced Techniques:</strong> Multi-pass washes, textured strokes &amp; ombre depth on denim, jackets &amp; linen.</span></li>
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>Signature Style &amp; Composition:</strong> Discovering your motif language, freehand sketching and pattern balance.</span></li>
                                <li><span class="tile-check-icon"><i class="bi bi-check-lg"></i></span> <span><strong>Multi-Piece Collection:</strong> Paint jackets, denim, tote bags and statement wardrobe pieces.</span></li>
                                <li class="palette-perk">
                                    <div class="palette-perk-badge">
                                        <i class="bi bi-palette-fill"></i>
                                        <span><strong>Offline 5-Day Exclusive:</strong> Includes physical <em>Colour Palettes</em> &amp; curated studio painting kit to keep!</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="tile-bottom">
                        <div class="tile-price-box">
                            <div class="tile-price-main">
                                <span class="price-val">₹899 <small class="text-muted" style="font-size: 14px; font-weight: normal;">– ₹1,299</small></span>
                                <span class="price-unit">/ participant</span>
                            </div>
                            <span class="tile-mode-pill"><i class="bi bi-award"></i> Comprehensive 5-Day Mastery</span>
                        </div>
                        <a class="btn-tile-cta btn-tile-maroon" href="registration.php?workshop=5-day">
                            <span>Book 5-Day Mastery</span> <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- SECTION 05: ALL-INCLUSIVE STUDIO KIT SHOWCASE -->
    <section class="section-space studio-kit-section" id="studio-kit">
        <div class="container">
            <div class="split-heading reveal">
                <div class="section-label"><span>05</span><span class="line"></span><span>All materials included</span></div>
                <span class="artisan-badge-pill"><i class="bi bi-box2-heart text-terracotta"></i> 100% Studio Supplies Provided</span>
            </div>

            <div class="kit-header-row reveal reveal-delay-1">
                <div class="kit-header-copy">
                    <span class="eyebrow">Studio Gear &amp; Supplies</span>
                    <h2>Everything you need to create.<br><em>All on your table.</em></h2>
                    <p class="kit-lead">You never have to hunt for specialty fabric inks, chemical mediums, or precision brushes. We curate, test, and provide professional-grade supplies for every maker.</p>
                </div>
                <div class="kit-header-note">
                    <div class="kit-note-card">
                        <i class="bi bi-stars text-warning fs-4"></i>
                        <div>
                            <strong>No Shopping List Needed</strong>
                            <p class="mb-0">Walk in empty-handed; walk out with finished wearable art, professional pigments, and your custom palette.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2 kit-grid">
                <!-- Card 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="kit-card reveal">
                        <div class="kit-card-icon-wrap bg-soft-maroon">
                            <i class="bi bi-palette-fill"></i>
                        </div>
                        <div class="kit-card-body">
                            <span class="kit-card-num">01 / Inks</span>
                            <h4>12x Curated Textile Pigments</h4>
                            <p>Premium non-toxic German textile emulsions that chemically bond into natural fibers without stiffening the cloth.</p>
                            <div class="kit-card-foot">
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Soft-touch</span>
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Fade-proof</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="kit-card reveal reveal-delay-1">
                        <div class="kit-card-icon-wrap bg-soft-terracotta">
                            <i class="bi bi-brush-fill"></i>
                        </div>
                        <div class="kit-card-body">
                            <span class="kit-card-num">02 / Brushes</span>
                            <h4>5x Precision Artist Brushes</h4>
                            <p>Curated set including wide flat wash brushes for large fills, filberts for organic floral petals, and 00 liner brushes for fine lines.</p>
                            <div class="kit-card-foot">
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Mixed-hair</span>
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Zero shedding</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="kit-card reveal reveal-delay-2">
                        <div class="kit-card-icon-wrap bg-soft-sand">
                            <i class="bi bi-bag-heart-fill"></i>
                        </div>
                        <div class="kit-card-body">
                            <span class="kit-card-num">03 / Canvas</span>
                            <h4>Heavyweight Canvas Tote Bag</h4>
                            <p>340 GSM unbleached natural organic cotton bag with reinforced shoulder webbing. Pre-washed and ready to paint.</p>
                            <div class="kit-card-foot">
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Provided to keep</span>
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> 340 GSM pure</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="kit-card reveal">
                        <div class="kit-card-icon-wrap bg-soft-sage">
                            <i class="bi bi-droplet-half"></i>
                        </div>
                        <div class="kit-card-body">
                            <span class="kit-card-num">04 / Medium</span>
                            <h4>Textile Medium &amp; Wash-Fixer</h4>
                            <p>Specialized fluid medium that improves paint glide and fixes pigments for 100% washing-machine resistance up to 30°C.</p>
                            <div class="kit-card-foot">
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Heat-cured</span>
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Crack-free</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="kit-card reveal reveal-delay-1">
                        <div class="kit-card-icon-wrap bg-soft-gold">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div class="kit-card-body">
                            <span class="kit-card-num">05 / Palette</span>
                            <h4>Studio Palette &amp; Harmony Guide</h4>
                            <p>Smooth hardwood studio mixing palette and laminated color temperature harmony wheel for effortless custom shading.</p>
                            <div class="kit-card-foot">
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Color wheel</span>
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Mixing recipes</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="kit-card reveal reveal-delay-2">
                        <div class="kit-card-icon-wrap bg-soft-slate">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <div class="kit-card-body">
                            <span class="kit-card-num">06 / Certificate</span>
                            <h4>Signed Studio Certificate</h4>
                            <p>Official BOLSO Certificate of Completion signed by instructor Abhishek, plus a laminated garment washing &amp; preservation guide.</p>
                            <div class="kit-card-foot">
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Signed credential</span>
                                <span class="kit-chip"><i class="bi bi-check-circle-fill text-success"></i> Care manual</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space guidance-section">
        <div class="container guidance-grid">
            <div class="guidance-art reveal">
                <div class="card-stack-container guidance-stack-container" id="guidanceCardStackSlider" data-card-stack>
                    <div class="card-stack-track">
                        <div class="card-stack-item" data-index="0">
                            <img src="assets/images/learn_beginner.jpg" alt="Beginner-friendly fabric painting techniques">
                            <div class="card-tag">01 · Beginner Techniques</div>
                        </div>
                        <div class="card-stack-item" data-index="1">
                            <img src="assets/images/learn_guidance.jpg" alt="Live instructor-led fabric guidance">
                            <div class="card-tag">02 · Live Guidance</div>
                        </div>
                        <div class="card-stack-item" data-index="2">
                            <img src="assets/images/learn_practice.jpg" alt="Hands-on fabric painting practice">
                            <div class="card-tag">03 · Hands-on Practice</div>
                        </div>
                        <div class="card-stack-item" data-index="3">
                            <img src="assets/images/learn_technique.jpg" alt="Fabric brush and pigment blending">
                            <div class="card-tag">04 · Brush Techniques</div>
                        </div>
                        <div class="card-stack-item" data-index="4">
                            <img src="assets/images/learn_colorwash.jpg" alt="Natural dye washes on stretched linen">
                            <div class="card-tag">05 · Dye &amp; Washes</div>
                        </div>
                        <div class="card-stack-item" data-index="5">
                            <img src="assets/images/learn_craft.jpg" alt="Artisan wearable finished craft samples">
                            <div class="card-tag">06 · Wearable Craft</div>
                        </div>
                    </div>

                    <!-- Navigation Controls & Indicators -->
                    <div class="card-stack-controls">
                        <button type="button" class="stack-arrow-btn stack-prev" aria-label="Previous card">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="stack-indicators">
                            <span class="stack-dot active" data-index="0"></span>
                            <span class="stack-dot" data-index="1"></span>
                            <span class="stack-dot" data-index="2"></span>
                            <span class="stack-dot" data-index="3"></span>
                            <span class="stack-dot" data-index="4"></span>
                            <span class="stack-dot" data-index="5"></span>
                        </div>
                        <button type="button" class="stack-arrow-btn stack-next" aria-label="Next card">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="handwritten">softly<br>boldly<br>you.</div>
            </div>
            <div class="guidance-copy reveal reveal-delay-1">
                <div class="section-label"><span>06</span><span class="line"></span><span>Learn by making</span></div>
                <h2>Personal guidance,<br><em>real practice.</em></h2>
                <p>Small groups mean there’s room for every question, every happy accident and every “can I try this?” moment.</p>
                <div class="guidance-feature-grid">
                    <div class="guidance-feature-card">
                        <div class="feature-card-icon"><i class="bi bi-palette"></i></div>
                        <div class="feature-card-text">
                            <span class="feature-card-num">01</span>
                            <h4>Beginner-Friendly Techniques</h4>
                            <p>Soft washable pigments, step-by-step canvas prep &amp; zero cracking guarantee.</p>
                        </div>
                    </div>
                    <div class="guidance-feature-card">
                        <div class="feature-card-icon"><i class="bi bi-brush"></i></div>
                        <div class="feature-card-text">
                            <span class="feature-card-num">02</span>
                            <h4>Hands-On Painting Practice</h4>
                            <p>Paint on real cloth, not scrap theory. Master ombre blending and fluid strokes.</p>
                        </div>
                    </div>
                    <div class="guidance-feature-card">
                        <div class="feature-card-icon"><i class="bi bi-person-video3"></i></div>
                        <div class="feature-card-text">
                            <span class="feature-card-num">03</span>
                            <h4>Live Instructor Mentorship</h4>
                            <p>Intimate cohorts capped at 6 students for real-time 1-on-1 guidance.</p>
                        </div>
                    </div>
                    <div class="guidance-feature-card">
                        <div class="feature-card-icon"><i class="bi bi-gem"></i></div>
                        <div class="feature-card-text">
                            <span class="feature-card-num">04</span>
                            <h4>A Wearable Masterpiece</h4>
                            <p>Finish a custom, machine-washable garment or bag you'll love wearing daily.</p>
                        </div>
                    </div>
                </div>
                <div class="guidance-actions">
                    <a class="btn btn-primary-bolso guidance-cta-btn" href="registration.php">
                        <span>I’m ready to make</span> <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- STUDENT REVIEWS & STORIES -->
    <section class="section-space reviews-section" style="background: #ffffff; border-top: 1px solid #ebd9c8; border-bottom: 1px solid #ebd9c8;">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">Student Stories</span>
                <h2>Made with their own hands.</h2>
                <p class="text-muted mx-auto" style="max-width: 540px;">See what our students created in their very first fabric painting sessions.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="h-100 p-4 rounded-4 reveal" style="background: #fbf7ef; border: 1px solid #ebd9c8;">
                        <div class="d-flex text-warning mb-3">
                            <i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-4" style="font-size: 14.5px; line-height: 1.6; color: #2e3842;">“I had never painted on clothes before. Abhishek made the brushwork and shading so approachable. My custom botanical tote bag is now my daily carryall!”</p>
                        <div class="d-flex align-items-center gap-3 pt-3 border-top border-dark border-opacity-10">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 42px; height: 42px; background: #7c2639; font-size: 15px;">P</div>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 14px;">Priya Sen</strong>
                                <small class="text-muted">Beginner · 2-Day Workshop</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="h-100 p-4 rounded-4 reveal reveal-delay-1" style="background: #fbf7ef; border: 1px solid #ebd9c8;">
                        <div class="d-flex text-warning mb-3">
                            <i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-4" style="font-size: 14.5px; line-height: 1.6; color: #2e3842;">“The physical colour palettes and German soft-touch inks in the 5-day workshop were incredible. The painted jacket has survived 6 washes and still looks freshly painted.”</p>
                        <div class="d-flex align-items-center gap-3 pt-3 border-top border-dark border-opacity-10">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 42px; height: 42px; background: #c25e3e; font-size: 15px;">R</div>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 14px;">Rahul Mukherjee</strong>
                                <small class="text-muted">Fashion Student · 5-Day Mastery</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="h-100 p-4 rounded-4 reveal reveal-delay-2" style="background: #fbf7ef; border: 1px solid #ebd9c8;">
                        <div class="d-flex text-warning mb-3">
                            <i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill me-1"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-4" style="font-size: 14.5px; line-height: 1.6; color: #2e3842;">“The intimate batch of only 6 students makes all the difference. You get real 1-on-1 guidance rather than getting lost in a crowded hall. Pure creative joy!”</p>
                        <div class="d-flex align-items-center gap-3 pt-3 border-top border-dark border-opacity-10">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width: 42px; height: 42px; background: #2f5d34; font-size: 15px;">A</div>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 14px;">Ananya Das</strong>
                                <small class="text-muted">Studio Attendee · Kolkata Batch</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container cta-inner">
            <span class="eyebrow">Your next favourite thing</span>
            <h2>Bring a little more<br><em>feeling</em> to fabric.</h2>
            <p class="cta-sub">Small batches · All materials included · Kolkata studio &amp; live online</p>
            <a class="btn btn-light-bolso cta-btn" href="registration.php">
                <span>Reserve your spot</span> <i class="bi bi-arrow-up-right"></i>
            </a>
            <div class="cta-doodle">✳</div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>