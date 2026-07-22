<section id="dashboard" class="dashboard-grid dashboard-landing">
    <div class="landing-slideshow<?= empty($displaySettings['panning_enabled']) ? ' no-pan' : '' ?>" data-landing-slideshow data-loop-duration="<?= e((string) ($displaySettings['loop_duration'] ?? 7)) ?>">
        <?php foreach ($slides as $index => $slide): ?>
            <figure class="landing-slide<?= $index === 0 ? ' is-active' : '' ?>">
                <img <?= $index === 0 ? 'src' : 'data-src' ?>="<?= e($slide['display_path']) ?>" alt="" <?= $index === 0 ? 'fetchpriority="high"' : '' ?>>
                <div class="landing-caption-stack">
                    <figcaption><span class="landing-caption-title"><?= e($slide['title']) ?></span><span>Photo: <?= e($slide['photographer_name']) ?><?= !empty($slide['location']) ? ' - ' . e($slide['location']) : '' ?><?= ($slide['source'] ?? '') === 'Pexels' ? ' - pexels.com' : '' ?></span></figcaption>
                    <a class="landing-feature-link" href="<?= !empty($_SESSION['user_id']) ? 'index.php?page=account#display-settings' : 'index.php?show_login=1' ?>"><strong>Feature your photo here!</strong><small>Submit a photo</small></a>
                </div>
            </figure>
        <?php endforeach; ?>
    </div>
    <?php if (empty($_SESSION['user_id'])): ?>
        <header class="landing-brand" aria-label="National Food Authority Farmer-Seller Registry">
            <img src="assets/images/farmer-seller-registry-logo-optimized.webp" width="256" height="256" alt="">
            <p>National Food Authority</p>
            <h1>Farmer-Seller Registry</h1>
        </header>
        <div class="landing-guest-actions">
            <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
            <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
        </div>
    <?php endif; ?>
    <div class="section-head landing-activity-head">
        <div><h4>Select an activity to start</h4></div>
    </div>

    <div class="dashboard-actions">
        <a class="action-square activity-transition" href="index.php?page=encode-farmer">
            <span class="activity-image-stack"><img class="activity-image base" src="assets/images/activity-buttons/button1-a1-v.png" alt=""><img class="activity-image hover" src="assets/images/activity-buttons/button1-a1-h.png" alt=""></span>
            <strong>Create Farmer Profile</strong>
        </a>
        <a class="action-square activity-transition" href="index.php?page=individual-delivery">
            <span class="activity-image-stack"><img class="activity-image base" src="assets/images/activity-buttons/button2-a2-v.png" alt=""><img class="activity-image hover" src="assets/images/activity-buttons/button2-a2-h.png" alt=""></span>
            <strong>Individual Farmer Delivery</strong>
        </a>
        <a class="action-square activity-transition" href="index.php?page=organization-delivery">
            <span class="activity-image-stack"><img class="activity-image base" src="assets/images/activity-buttons/button3-a3-h.png" alt=""><img class="activity-image hover" src="assets/images/activity-buttons/button3-a3-v.png" alt=""></span>
            <strong>Farmers Organization Delivery</strong>
        </a>
    </div>
</section>
