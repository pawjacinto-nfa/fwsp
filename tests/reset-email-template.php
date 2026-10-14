<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
$content = \App\Support\ResetMailer::content('Sample User', '123456', 'https://sandbox.nfa.gov.ph/fsr/index.php?password_reset_link=' . str_repeat('a', 64), true);
$unsafe = \App\Support\ResetMailer::content('<script>alert(1)</script>', '123456', 'https://example.invalid/?a=1&b=2');
if (str_contains($unsafe['html'], '<script>') || !str_contains($unsafe['html'], '&lt;SCRIPT&gt;')
    || !str_contains($content['html'], 'HI, SAMPLE USER')
    || !str_contains($content['text'], 'Yours,\nFSR System Administrator') && !str_contains($content['text'], "Yours,\nFSR System Administrator")) {
    fwrite(STDERR, "Template validation failed.\n"); exit(1);
}
$preview = str_replace(['cid:fsr-reset-header', 'cid:fsr-reset-footer'],
    ['../assets/images/email/reset-header.png', '../assets/images/email/reset-footer.png'], $content['html']);
file_put_contents(BASE_PATH . '/output/reset-email-preview.html', $preview);
echo "PASS: Dynamic name, code, link, plain-text fallback and HTML escaping. Preview saved.\n";
