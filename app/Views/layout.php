<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NFA-FSR: <?= e($title ?? 'Farmer-Seller Registry') ?></title>
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="loader-screen" id="loaderScreen" aria-hidden="true">
    <div class="palay-loader">
        <img src="assets/images/fwsp-loader.gif" width="96" height="96" alt="">
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/nav.php'; ?>

<main class="app-shell">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= e($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($alert['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<?php require BASE_PATH . '/app/Views/partials/auth-modals.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.FWSP_LOCATIONS = <?= json_encode(\App\Models\Location::hierarchy(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
window.FWSP_CENTRAL_OFFICE = <?= json_encode(\App\Models\CentralOffice::hierarchy(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
window.FWSP_IS_AUTHENTICATED = <?= !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
window.FWSP_AUTH_MODAL = <?= json_encode([
    'showLogin' => isset($_GET['show_login']),
    'showForgotPassword' => isset($_GET['forgot_password']),
    'showChangePassword' => !empty($_SESSION['password_reset_user_id']) || isset($_GET['password_reset']),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
