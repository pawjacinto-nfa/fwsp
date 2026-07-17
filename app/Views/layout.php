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
    <link href="assets/css/style.css?v=<?= e((string) filemtime(BASE_PATH . '/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="loader-screen" id="loaderScreen" aria-hidden="true">
    <div class="palay-loader">
        <img src="assets/images/fwsp-loader.gif" width="96" height="96" alt="">
    </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/nav.php'; ?>

<main class="app-shell">
    <?= $content ?>
</main>

<?php require BASE_PATH . '/app/Views/partials/auth-modals.php'; ?>

<?php if (!empty($alert)): ?>
    <?php
    $flashType = in_array($alert['type'] ?? '', ['success', 'danger', 'warning', 'info'], true) ? $alert['type'] : 'info';
    $flashTitles = [
        'success' => 'Success',
        'danger' => 'Error',
        'warning' => 'Attention',
        'info' => 'Notice',
    ];
    $flashMessage = (string) ($alert['message'] ?? '');
    $isWelcomeMessage = !empty($_SESSION['user']) && str_starts_with($flashMessage, 'Welcome back, ');
    ?>
    <div class="modal fade auth-modal flash-message-modal" id="flashMessageModal" tabindex="-1" aria-labelledby="flashMessageModalTitle" aria-hidden="true" data-flash-message-modal>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="flashMessageModalTitle"><?= e($flashTitles[$flashType]) ?></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body flash-message-body<?= $isWelcomeMessage ? ' flash-message-welcome' : '' ?>">
                    <?php if ($isWelcomeMessage): ?>
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                            <img class="flash-welcome-avatar" src="<?= e($_SESSION['profile_image']) ?>" alt="">
                        <?php else: ?>
                            <span class="flash-welcome-avatar flash-welcome-avatar-fallback" aria-hidden="true"><?= e(substr((string) $_SESSION['user'], 0, 1)) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="flash-message-icon flash-message-<?= e($flashType) ?>" aria-hidden="true">!</span>
                    <?php endif; ?>
                    <p><?= e($flashMessage) ?></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="button" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade auth-modal" id="confirmActionModal" tabindex="-1" aria-hidden="true" data-confirm-action-modal>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content confirm-action-modal">
            <div class="modal-header">
                <h2 class="modal-title fs-5" data-confirm-title>Confirm Action</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" data-confirm-message>Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" type="button" data-confirm-accept>Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.FWSP_LOCATIONS = <?= json_encode(\App\Models\Location::hierarchy(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
window.FWSP_CENTRAL_OFFICE = <?= json_encode(\App\Models\CentralOffice::hierarchy(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
window.FWSP_IS_AUTHENTICATED = <?= !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
<?php $offlineUser = !empty($_SESSION['user_id']) ? \App\Models\User::find((int) $_SESSION['user_id']) : null; ?>
window.FWSP_OFFLINE = <?= json_encode([
    'enabled' => !empty($offlineUser['offline_enabled']),
    'userId' => (int) ($_SESSION['user_id'] ?? 0),
    'csrfToken' => csrf_token(),
    'syncUrl' => 'index.php',
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
window.FWSP_AUTH_MODAL = <?= json_encode([
    'showLogin' => isset($_GET['show_login']),
    'showRegister' => isset($_GET['show_register']),
    'showForgotPassword' => isset($_GET['forgot_password']),
    'showChangePassword' => !empty($_SESSION['password_reset_user_id']) || isset($_GET['password_reset']),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="assets/js/app.js?v=<?= e((string) filemtime(BASE_PATH . '/assets/js/app.js')) ?>"></script>
</body>
</html>
