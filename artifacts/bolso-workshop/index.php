<?php
declare(strict_types=1);

$pageTitle = 'Learn to turn fabric into art';
$activePage = 'home';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero-section">
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
                <div class="hero-stamp"><span>Make it<br>wearable</span><i class="bi bi-stars"></i></div>
            </div>
        </div>
        <div class="hero-bottom-line"><span>01</span><span class="line"></span><span>Art · Emotion · Fashion.</span></div>
    </section>

    <section class="marquee-strip" aria-label="BOLSO principles">
        <div class="marquee-track"><span>MAKE SOMETHING SOFT</span><i>✳</i><span>MAKE SOMETHING YOU</span><i>✳</i><span>MAKE SOMETHING TO WEAR</span><i>✳</i><span>MAKE SOMETHING SOFT</span><i>✳</i></div>
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

    <section class="workshop-preview section-space">
        <div class="container">
            <div class="split-heading">
                <div class="section-label"><span>03</span><span class="line"></span><span>Choose your pace</span></div>
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
                <div class="section-label"><span>04</span><span class="line"></span><span>Learn by making</span></div>
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