<?php
declare(strict_types=1);

$pageTitle = 'Workshops';
$activePage = 'workshops';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="page-hero">
        <div class="container page-hero-inner">
            <div class="section-label"><span>01</span><span class="line"></span><span>Workshop menu</span></div>
            <h1>Choose your<br><em>kind of making.</em></h1>
            <p>Two ways to get your hands into the colour. Both are small, personal and made for starting exactly where you are.</p>
        </div>
    </section>
    <section class="workshops-list section-space">
        <div class="container">
            <article class="workshop-detail workshop-detail-dark reveal">
                <div class="detail-number">01</div>
                <div class="detail-main">
                    <span class="eyebrow">A confident first step</span>
                    <h2>2-Day<br><em>Workshop</em></h2>
                    <p>Get the essentials under your fingers and finish your first wearable fabric painting with calm, personal support.</p>
                    <div class="detail-meta"><span><strong>3–3.5 hrs</strong> each session</span><span><strong>Maximum 6</strong> students</span><span><strong>Online or offline</strong> same price</span></div>
                </div>
                <div class="detail-side">
                    <span class="price-label">from</span><strong class="detail-price">₹399</strong><small>Online ₹399<br>Offline ₹399</small>
                    <a class="btn btn-light-bolso" href="registration.php?workshop=2-day">Reserve 2-day <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </article>
            <article class="workshop-detail workshop-detail-sand reveal">
                <div class="detail-number">02</div>
                <div class="detail-main">
                    <span class="eyebrow">Take your time</span>
                    <h2>5-Day<br><em>Workshop</em></h2>
                    <p>Slow down, build a visual language and learn to make work that feels unmistakably like yours.</p>
                    <div class="detail-meta"><span><strong>5 days</strong> of practice</span><span><strong>Live instructor</strong> led classes</span><span><strong>Maximum 6</strong> students</span></div>
                </div>
                <div class="detail-side"><span class="price-label">choose your mode</span><div class="mode-price"><strong>₹899</strong><span>Online</span></div><div class="mode-price"><strong>₹1,299</strong><span>Offline</span></div><a class="btn btn-primary-bolso" href="registration.php?workshop=5-day">Reserve 5-day <i class="bi bi-arrow-up-right"></i></a></div>
            </article>
        </div>
    </section>
    <section class="section-space learn-section">
        <div class="container">
            <div class="section-label"><span>02</span><span class="line"></span><span>Every workshop includes</span></div>
            <div class="learn-grid">
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_beginner.jpg" alt="Beginner-friendly">
                    <span>✦</span>
                    <h3>Beginner-friendly</h3>
                    <p>No prior experience needed. Just bring curiosity.</p>
                </div>
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_technique.jpg" alt="Technique first">
                    <span>◌</span>
                    <h3>Technique first</h3>
                    <p>Learn how colour moves, settles and stays soft on fabric.</p>
                </div>
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_practice.jpg" alt="Hands-on practice">
                    <span>⌁</span>
                    <h3>Hands-on practice</h3>
                    <p>Less watching, more making. Your hands will remember.</p>
                </div>
                <div class="learn-card">
                    <img class="learn-img" src="assets/images/learn_guidance.jpg" alt="Personal guidance">
                    <span>✺</span>
                    <h3>Personal guidance</h3>
                    <p>A small batch means you’re never painting alone.</p>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>