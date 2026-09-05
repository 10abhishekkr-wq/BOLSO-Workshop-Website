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
                    <a class="btn btn-primary-bolso" href="registration.php">Reserve your spot <i class="bi bi-arrow-up-right"></i></a>
                    <a class="text-link" href="#about">Discover BOLSO <i class="bi bi-arrow-down"></i></a>
                </div>
                <div class="hero-proof"><strong>Only 6</strong><span>students per batch</span><i></i><strong>2–5 days</strong><span>of guided making</span></div>
            </div>
            <div class="hero-art reveal reveal-delay-1">
                <div class="art-orbit orbit-one"></div>
                <div class="art-orbit orbit-two"></div>
                <div class="art-swatch swatch-rust"></div>
                <div class="art-swatch swatch-blue"></div>
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
                <div class="about-heading reveal"><h2>There is art<br>in the <em>everyday.</em></h2></div>
                <div class="about-copy reveal reveal-delay-1">
                    <p class="lead-copy">A plain tee. A canvas tote. Your favourite old pair of jeans. We believe the things closest to us deserve a little more feeling.</p>
                    <p>BOLSO started with custom fabric paintings and grew into a place to learn the process for yourself. From the first brushstroke to the last soft wash, you’ll learn how to make colour sit beautifully on cloth.</p>
                    <a class="text-link dark-link" href="workshops.php">See what you’ll learn <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="material-row">
                <div class="material-item"><span class="material-number">01</span><img class="material-img" src="img3.png" alt=""><span class="material-icon">✦</span><strong>T-shirts</strong><small>Make your daily uniform yours.</small></div>
                <div class="material-item"><span class="material-number">02</span><img   class="material-img"src="img4.png" alt=""><span class="material-icon">◌</span><strong>Bags</strong><small>Carry a little bit of joy.</small></div>
                <div class="material-item"><span class="material-number">03</span><img  class="material-img"  src="img2.png" alt=""><span class="material-icon">⌁</span><strong>Denim</strong><small>Give old favourites new life.</small></div>
                <div class="material-item"><span class="material-number">04</span><img   class="material-img" src="img1.png" alt=""><span class="material-icon">✺</span><strong>Anything fabric</strong><small>There are no wrong canvases.</small></div>
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
                                <li><i class="bi bi-check2"></i> <span><strong>Fabric &amp; Pigment Basics:</strong> Prepping cotton, denim &amp; linen for smooth, crack-free colour absorption.</span></li>
                                <li><i class="bi bi-check2"></i> <span><strong>Brushwork &amp; Shading:</strong> Fluid brush control, blending gradients and soft-wash layering.</span></li>
                                <li><i class="bi bi-check2"></i> <span><strong>First Finished Garment:</strong> Paint and finish your own wearable custom T-shirt or canvas tote.</span></li>
                                <li><i class="bi bi-check2"></i> <span><strong>Personal Mentorship:</strong> Live step-by-step guidance in intimate batches (max 6 students).</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="tile-bottom">
                        <div class="tile-price-wrap">
                            <span class="price-val">From ₹399</span>
                            <span class="price-sub">Online &amp; Offline</span>
                        </div>
                        <a href="registration.php?workshop=2-day">Book this <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                </article>
                <article class="workshop-tile tile-sand reveal reveal-delay-1">
                    <div class="tile-top"><span class="tile-tag">The deep dive</span><span>02 / 02</span></div>
                    <div class="tile-middle">
                        <p class="tile-days">5 days · Complete mastery</p>
                        <h3>Find your<br><em>signature.</em></h3>
                        <p class="tile-desc">Immerse yourself in comprehensive fabric art. Build a distinct visual language and create a signature wearable collection.</p>
                        <div class="tile-learn-wrap">
                            <span class="tile-learn-title">What you'll learn:</span>
                            <ul class="tile-learn-points">
                                <li><i class="bi bi-check2"></i> <span><strong>Advanced Techniques:</strong> Multi-pass washes, textured strokes &amp; ombre depth on denim, jackets &amp; linen.</span></li>
                                <li><i class="bi bi-check2"></i> <span><strong>Signature Style &amp; Composition:</strong> Discovering your motif language, freehand sketching and pattern balance.</span></li>
                                <li><i class="bi bi-check2"></i> <span><strong>Multi-Piece Collection:</strong> Paint jackets, denim, tote bags and statement wardrobe pieces.</span></li>
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
                        <div class="tile-price-wrap">
                            <span class="price-val">From ₹899</span>
                            <span class="price-sub">Online ₹899 · Offline ₹1,299</span>
                        </div>
                        <a href="registration.php?workshop=5-day">Book this <i class="bi bi-arrow-up-right"></i></a>
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
                <ul class="check-list">
                    <li><span>01</span> Beginner-friendly techniques</li>
                    <li><span>02</span> Hands-on painting practice</li>
                    <li><span>03</span> Live instructor-led guidance</li>
                    <li><span>04</span> A wearable piece to be proud of</li>
                </ul>
                <a class="btn btn-outline-bolso" href="registration.php">I’m ready to make <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container cta-inner">
            <span class="eyebrow">Your next favourite thing</span>
            <h2>Bring a little more<br><em>feeling</em> to fabric.</h2>
            <a class="btn btn-light-bolso" href="registration.php">Reserve your spot <i class="bi bi-arrow-up-right"></i></a>
            <div class="cta-doodle">✳</div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>