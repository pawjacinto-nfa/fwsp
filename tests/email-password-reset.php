<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/reset-mail-fixture.php';
require dirname(__DIR__) . '/app/bootstrap.php';
set_exception_handler(static function (Throwable $error): never { fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1); });
use App\Core\Database;
use App\Models\User;
use App\Support\ResetMailer;
function check(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException('FAIL: ' . $message);
    echo 'PASS: ' . $message . PHP_EOL;
}
$db = Database::connection();
User::findByUsername('__schema_only__');
$name = 'RESET_TEST_' . bin2hex(random_bytes(6));
$stmt = $db->prepare("INSERT INTO users (full_name,username,email,password_hash,role,is_active,status)
    VALUES ('Reset test',?,'reset@example.invalid','unused','Read-Only User',1,'Active')");
$stmt->execute([$name]);
$id = (int) $db->lastInsertId();
function reissue(int $id): array {
    Database::connection()->exec('UPDATE users SET password_reset_requested_at=NULL WHERE id=' . $id);
    check(User::requestPasswordReset($id), 'Eligible request issues email');
    return ResetMailer::$last;
}
try {
    $mail = reissue($id);
    $user = User::find($id);
    check($mail['email'] === 'reset@example.invalid', 'Email uses registered recipient');
    check(preg_match('/^[0-9]{6}$/D', $mail['code']) === 1 && password_verify($mail['code'], $user['password_reset_code_hash']), 'Six-digit code stored as a password hash');
    check($user['password_reset_link_hash'] === hash('sha256', $mail['link']), 'Random link stored only as a hash');
    check(User::resetLinkUsername($mail['link']) === $name, 'Valid link locates confirmation form');
    check(!User::resetTokenValid($user, $mail['link']), 'Opening link alone does not authorize reset');
    check(!User::requestPasswordReset($id), 'Resend cooldown enforced');
    $seconds = (int) $db->query('SELECT TIMESTAMPDIFF(SECOND,NOW(),password_reset_expires_at) FROM users WHERE id=' . $id)->fetchColumn();
    check($seconds > 880 && $seconds <= 900, 'Expiry is 15 minutes using database clock');
    $wrong = $mail['code'] === '000000' ? '000001' : '000000';
    for ($i=0; $i<5; $i++) check(User::verifyResetCode($id, $wrong) === '', 'Incorrect code rejected');
    check(User::verifyResetCode($id, $mail['code']) === '' && User::resetLinkUsername($mail['link']) === '', 'Five attempts lock the request');
    $old = $mail;
    $mail = reissue($id);
    check(User::resetLinkUsername($old['link']) === '', 'Resend invalidates old link');
    $grant = User::verifyResetCode($id, $mail['code']);
    check(strlen($grant) === 64 && User::resetTokenValid(User::find($id), $grant), 'Correct code grants a session reset token');
    check(User::verifyResetCode($id, $mail['code']) === '', 'Confirmation code is single-use');
    check(!User::completePasswordReset($id, 'New-test-passphrase', $mail['code']), 'Code cannot bypass verification stage');
    check(!User::completePasswordReset($id, 'short', $grant), 'Password policy enforced');
    check(User::completePasswordReset($id, 'New-test-passphrase', $grant), 'Verified grant changes password');
    check(password_verify('New-test-passphrase', User::find($id)['password_hash']), 'Password actually changed');
    check(!User::completePasswordReset($id, 'Replay-test-passphrase', $grant), 'Grant cannot be replayed');
    $mail = reissue($id);
    $db->exec('UPDATE users SET password_reset_expires_at=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE id=' . $id);
    check(User::verifyResetCode($id, $mail['code']) === '' && User::resetLinkUsername($mail['link']) === '', 'Expired code and link rejected');
    $mail = reissue($id);
    $grant = User::verifyResetCode($id, $mail['code']);
    reissue($id);
    check(!User::completePasswordReset($id, 'Stale-grant-passphrase', $grant), 'Resend invalidates verified grants');
    $db->exec('UPDATE users SET is_active=0 WHERE id=' . $id);
    check(!User::requestPasswordReset($id), 'Inactive account cannot request reset');
    $db->exec('UPDATE users SET is_active=1,password_reset_requested_at=NULL WHERE id=' . $id);
    ResetMailer::$fail = true;
    try { User::requestPasswordReset($id); check(false, 'Mail failure must throw'); } catch (RuntimeException $e) {
        check($e->getMessage() === 'Email delivery failed.', 'Mail failure reported without credentials');
    }
    $user = User::find($id);
    check($user['password_reset_code_hash'] === null && $user['password_reset_link_hash'] === null && $user['password_reset_status'] === null, 'Mail failure invalidates credentials');
} finally {
    $db->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
}
