<?php
declare(strict_types=1);

$pageTitle = 'Workshops & Classes';
$activePage = 'workshops';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container page-hero-inner">
            <div class="section-label"><span>01</span><span class="line"></span><span>Workshop Menu</span></div>
            <h1>Choose your<br><em>kind of making.</em></h1>
            <p>Two ways to get your hands into the colour. Both are small, personal and made for starting exactly where you are.</p>
            <div class="d-flex flex-wrap gap-3 mt-4">
                <a href="#workshops-list" class="btn btn-primary-bolso">View Workshops <i class="bi bi-arrow-down"></i></a>
                <a href="https://wa.me/919341469219?text=Hi%20BOLSO%20Studio!%20I%20have%20a%20question%20about%20the%20workshops." target="_blank" rel="noopener noreferrer" class="btn btn-outline-bolso">
                    <i class="bi bi-whatsapp text-success"></i> Ask on WhatsApp
                </a>
            </div>
        </div>
    </section>

    <!-- WORKSHOPS TILES -->
    <section class="workshops-list section-space" id="workshops-list">
        <div class="container">
            <!-- 2-Day Workshop Tile -->
            <article class="workshop-detail workshop-detail-dark reveal mb-5">
                <div class="detail-number">01</div>
                <div class="detail-main">
                    <span class="eyebrow">A confident first step</span>
                    <h2>2-Day<br><em>Workshop</em></h2>
                    <p>Get the essentials under your fingers and finish your first wearable fabric painting with calm, personal support.</p>
                    
                    <div class="detail-meta">
                        <span><i class="bi bi-clock me-1"></i> <strong>3–3.5 hrs</strong> each session</span>
                        <span><i class="bi bi-people me-1"></i> <strong>Maximum 6</strong> students</span>
                        <span><i class="bi bi-laptop me-1"></i> <strong>Online or Offline</strong> same price</span>
                    </div>

                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <strong class="d-block mb-2 text-white" style="font-size: 13px; letter-spacing: 0.05em; text-transform: uppercase;">What you'll complete:</strong>
                        <ul class="list-unstyled mb-0 text-white-50" style="font-size: 13.5px; line-height: 1.8;">
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Understand cotton &amp; canvas prep for crack-proof paint hold</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Master brush pressure, gradient wash &amp; clean line work</li>
                            <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Take home 1 completed custom wearable T-shirt or Canvas Tote</li>
                        </ul>
                    </div>
                </div>
                <div class="detail-side">
                    <span class="price-label">from</span>
                    <strong class="detail-price">₹399</strong>
                    <small>Online ₹399<br>Offline ₹399</small>
                    <span class="d-block mt-2 mb-3" style="font-size: 11.5px; opacity: 0.85;"><i class="bi bi-wallet2"></i> Offline: Pay online or at studio</span>
                    <a class="btn btn-light-bolso w-100" href="registration.php?workshop=2-day">Reserve 2-day <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </article>

            <!-- 5-Day Workshop Tile -->
            <article class="workshop-detail workshop-detail-sand reveal">
                <div class="detail-number">02</div>
                <div class="detail-main">
                    <span class="eyebrow">Comprehensive Art Mastery</span>
                    <h2>5-Day<br><em>Workshop</em></h2>
                    <p>Slow down, build a visual language and learn to make work that feels unmistakably like yours. Explore advanced pigments, shading &amp; multi-garment collections.</p>
                    
                    <div class="detail-meta">
                        <span><i class="bi bi-calendar2-check me-1"></i> <strong>5 days</strong> of immersion</span>
                        <span><i class="bi bi-person-video3 me-1"></i> <strong>Live instructor</strong> mentorship</span>
                        <span><i class="bi bi-people me-1"></i> <strong>Maximum 6</strong> students</span>
                    </div>

                    <div class="palette-perk-badge mt-3" style="max-width: 580px;">
                        <i class="bi bi-palette-fill"></i>
                        <span><strong>Offline 5-Day Workshop Bonus:</strong> Students attending the offline workshop receive physical <em>Colour Palettes</em> &amp; curated studio materials to keep!</span>
                    </div>

                    <div class="mt-4 pt-3 border-top border-dark border-opacity-10">
                        <strong class="d-block mb-2 text-dark" style="font-size: 13px; letter-spacing: 0.05em; text-transform: uppercase;">What you'll complete:</strong>
                        <ul class="list-unstyled mb-0 text-muted" style="font-size: 13.5px; line-height: 1.8;">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Advanced layering, ombre blending &amp; denim texture work</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Freehand motif development &amp; signature composition</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> 2–3 completed garments (Jacket, Jeans, Canvas Tote Bag)</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Professional heat-setting, fixation &amp; lifetime wash-care secrets</li>
                        </ul>
                    </div>
                </div>
                <div class="detail-side">
                    <span class="price-label">choose your mode</span>
                    <div class="mode-price"><strong>₹899</strong><span>Online</span></div>
                    <div class="mode-price"><strong>₹1,299</strong><span>Offline</span></div>
                    <span class="d-block mt-2 mb-3" style="font-size: 11.5px; color: var(--ink-soft);"><i class="bi bi-wallet2"></i> Offline: Pay online or at studio</span>
                    <a class="btn btn-primary-bolso w-100" href="registration.php?workshop=5-day">Reserve 5-day <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </article>
        </div>
    </section>

    <!-- WHAT EVERY WORKSHOP INCLUDES -->
    <section class="section-space learn-section bg-light bg-opacity-50">
        <div class="container">
            <div class="section-label"><span>02</span><span class="line"></span><span>The Studio Standard</span></div>
            <h2 class="mb-4">Every workshop includes</h2>
            <div class="learn-grid">
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_beginner.jpg" alt="Beginner-friendly fabric painting">
                    <span>✦</span>
                    <h3>Beginner-friendly</h3>
                    <p>No prior drawing or painting experience needed. Over 85% of our students hold a brush for the first time.</p>
                </div>
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_technique.jpg" alt="Soft-touch pigment technique">
                    <span>◌</span>
                    <h3>Technique first</h3>
                    <p>Learn how colour moves, settles and stays soft on fabric without stiffening or cracking after washing.</p>
                </div>
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_practice.jpg" alt="Hands-on making">
                    <span>⌁</span>
                    <h3>Hands-on practice</h3>
                    <p>Less watching, more making. You paint on authentic wearable cloth from the very first session.</p>
                </div>
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_guidance.jpg" alt="Personal guidance">
                    <span>✺</span>
                    <h3>Personal guidance</h3>
                    <p>Small cohorts capped at 6 students ensure you receive live, step-by-step 1-on-1 mentorship.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TIMINGS & VENUE CARD -->
    <section class="section-space">
        <div class="container">
            <div class="p-4 p-md-5 rounded-4 shadow-sm" style="background: #ffffff; border: 1px solid #ebd9c8;">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <span class="eyebrow">Studio Schedule &amp; Batches</span>
                        <h2 class="h3 mb-3">Convenient Morning &amp; Evening Batches</h2>
                        <p class="text-muted mb-4">Choose the slot that fits your pace. Both offline studio batches in Kolkata and live interactive online sessions are available weekly.</p>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-warning text-dark p-2 rounded-3"><i class="bi bi-sun-fill"></i></span>
                                <div>
                                    <strong>Morning Batch: 10:30 AM – 1:30 PM</strong>
                                    <div class="small text-muted">Fresh, quiet studio atmosphere with ample natural daylight.</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-3">
                                <span class="badge bg-primary p-2 rounded-3"><i class="bi bi-moon-stars-fill"></i></span>
                                <div>
                                    <strong>Evening Batch: 4:00 PM – 7:00 PM</strong>
                                    <div class="small text-muted">Perfect for working professionals and weekend relaxation.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="p-4 rounded-4" style="background: #fbf7ef; border: 1px dashed #c25e3e;">
                            <h4 class="h5 mb-2"><i class="bi bi-geo-alt-fill text-terracotta"></i> Kolkata Studio Address</h4>
                            <p class="small text-muted mb-3">Jayanti Abasan, Jhowtala Hatiara, Near Lokenath Mandir, Chinar Park, Kolkata - 700157</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://www.google.com/maps/search/?api=1&query=22.620363,88.440207" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-bolso">
                                    <i class="bi bi-map"></i> Open in Google Maps
                                </a>
                                <a href="registration.php" class="btn btn-sm btn-primary-bolso">
                                    <i class="bi bi-check2-circle"></i> Reserve Your Slot
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQS -->
    <section class="section-space pb-5">
        <div class="container" style="max-width: 860px;">
            <div class="text-center mb-5">
                <span class="eyebrow">Common Questions</span>
                <h2>Frequently Asked Questions</h2>
            </div>
            <div class="accordion bolso-accordion" id="workshopFaqs">
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Do I need any previous drawing or painting skills?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#workshopFaqs">
                        <div class="accordion-body text-muted" style="font-size: 14.5px;">
                            No! Over 85% of our workshop students are complete beginners. We provide easy-to-follow stencils, tracing techniques, and 1-on-1 brushwork guidance so you create a stunning wearable piece with zero stress.
                        </div>
                    </div>
                </div>

                <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Will the paint wash off in the washing machine?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#workshopFaqs">
                        <div class="accordion-body text-muted" style="font-size: 14.5px;">
                            Never. We use professional German-grade soft-touch textile pigments that chemically bond with the fabric fibers upon heat-setting. Your painted garments and tote bags can be machine washed regularly without cracking or fading.
                        </div>
                    </div>
                </div>

                <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            What materials should I bring to the workshop?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#workshopFaqs">
                        <div class="accordion-body text-muted" style="font-size: 14.5px;">
                            Nothing! All garments, cotton bags, specialized textile pigments, artist brushes, palettes, and protective aprons are provided for you at the studio. If you have a favorite old denim jacket or jeans you'd like to paint on, feel free to bring them along!
                        </div>
                    </div>
                </div>

                <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            How do online workshops work for participants outside Kolkata?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#workshopFaqs">
                        <div class="accordion-body text-muted" style="font-size: 14.5px;">
                            Online sessions are conducted live over Google Meet in small groups. You will receive a recommended materials list or curated painting kit recommendations beforehand, and Instructor Abhishek guides you live with overhead camera angles.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
