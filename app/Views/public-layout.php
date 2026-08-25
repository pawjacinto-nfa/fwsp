<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="referrer" content="no-referrer">
    <title>NFA-FSR: <?= e($title ?? 'Appointment Status') ?></title>
    <link rel="icon" href="<?= e(app_base_path()) ?>/favicon.ico" sizes="any">
    <link href="<?= e(app_base_path()) ?>/assets/css/delivery-schedule-status.css?v=<?= e((string) filemtime(BASE_PATH . '/assets/css/delivery-schedule-status.css')) ?>" rel="stylesheet">
</head>
<body>
    <?= $content ?>
</body>
</html>
