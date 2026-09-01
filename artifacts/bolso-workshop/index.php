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
                <div class="art-swatch swatch-mustard"></div>
                <img style="height:55vh;border-radius:85px;left:70vw;box-shadow: 8px 8px 13px gray;" src="image.png" alt="BOLSO fabric artwork placeholder" onerror="this.style.display='none'">
                <!-- <div class="hero-image-frame"> -->
                    <!-- <div class="image-placeholder">
                        <span class="placeholder-brush">B</span>
                        <small>Your artwork<br>could live here</small>
                    </div>
                    <span class="image-caption">Artwork / BOLSO studio</span> -->
                <!-- </div> -->
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
                <div class="material-item"><span class="material-number">01</span><img style="height=100px;width=100px;border-radius=30px;" src="img3.png" alt=""><span class="material-icon">✦</span><strong>T-shirts</strong><small>Make your daily uniform yours.</small></div>
                <div class="material-item"><span class="material-number">02</span><img style="height=100px;width=100px;border-radius=30px;" src="img4.png" alt=""><span class="material-icon">◌</span><strong>Bags</strong><small>Carry a little bit of joy.</small></div>
                <div class="material-item"><span class="material-number">03</span><img style="height=100px;width=100px;border-radius=30px;" src="img2.png" alt=""><span class="material-icon">⌁</span><strong>Denim</strong><small>Give old favourites new life.</small></div>
                <div class="material-item"><span class="material-number">04</span><img style="height=100px;width=100px;border-radius=30px;" src="img1.png" alt=""><span class="material-icon">✺</span><strong>Anything fabric</strong><small>There are no wrong canvases.</small></div>
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
                    <div class="tile-middle"><p class="tile-days">2 days</p><h3>Start with<br><em>the basics.</em></h3></div>
                    <div class="tile-bottom"><span>From ₹399</span><a href="registration.php?workshop=2-day">Book this <i class="bi bi-arrow-up-right"></i></a></div>
                </article>
                <article class="workshop-tile tile-sand reveal reveal-delay-1">
                    <div class="tile-top"><span class="tile-tag">The deep dive</span><span>02 / 02</span></div>
                    <div class="tile-middle"><p class="tile-days">5 days</p><h3>Find your<br><em>signature.</em></h3></div>
                    <div class="tile-bottom"><span>From ₹899</span><a href="registration.php?workshop=5-day">Book this <i class="bi bi-arrow-up-right"></i></a></div>
                </article>
            </div>
        </div>
    </section>

    <section class="section-space guidance-section">
        <div class="container guidance-grid">
            <div class="guidance-art reveal">
                <div class="guidance-backdrop"></div>
                <div class="guidance-card">
                    <img src="assets/images/work2.jpg" alt="BOLSO workshop artwork placeholder" onerror="this.style.display='none'">
                    <div class="image-placeholder small"><span class="placeholder-brush">✦</span><small>Made by hand,<br>made by you.</small></div>
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