<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
$email = $argv[1] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { fwrite(STDERR, "Supply a recipient email address.\n"); exit(1); }
try {
    \App\Support\ResetMailer::send($email, str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT), bin2hex(random_bytes(32)), true, $argv[2] ?? 'Sample User');
    echo "SMTP accepted the test message. Check the recipient inbox and spam folder.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "Test email could not be sent. Check the private SMTP settings and provider connection.\n");
    exit(1);
}
