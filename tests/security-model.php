<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || getenv('FSR_DB_NAME') !== 'fsr_security_verify_20261002') {
    exit("Run only against the isolated security verification database.\n");
}
require dirname(__DIR__) . '/app/bootstrap.php';
set_exception_handler(static function (Throwable $error): never { fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1); });
use App\Core\Database;
use App\Models\User;
function check(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException('FAIL: ' . $message);
    echo 'PASS: ' . $message . PHP_EOL;
}
$db = Database::connection();
User::findByUsername('__schema_only__');
$insert = $db->prepare("INSERT INTO users (full_name,username,email,password_hash,role,is_active,status) VALUES ('Security test',:username,:email,:password,:role,1,'Active')
    ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role=VALUES(role), is_active=1, status='Active', password_reset_status=NULL, password_reset_token_hash=NULL, password_reset_expires_at=NULL");
foreach (['SECURITY_VERIFY_ADMIN'=>'System Admin','SECURITY_VERIFY_USER'=>'Warehouse Personnel'] as $username=>$role) {
    $insert->execute(['username'=>$username,'email'=>$username.'@example.invalid','password'=>password_hash('Verify-only-passphrase-2026',PASSWORD_DEFAULT),'role'=>$role]);
}
$user = User::findByUsername('SECURITY_VERIFY_USER');
$id = (int) $user['id'];
User::requestPasswordReset($id);
$token = User::approvePasswordReset($id);
$user = User::find($id);
$wrongToken = $token === '000000' ? '000001' : '000000';
check(preg_match('/^[0-9]{6}$/', $token) === 1 && $user['password_reset_token_hash'] !== $token, 'Reset token generated and stored only as a hash');
check(!User::resetTokenValid($user, ''), 'Username alone cannot claim reset');
check(!User::resetTokenValid($user, $wrongToken), 'Incorrect reset token rejected');
check(User::resetTokenValid($user,$token), 'Correct approved token accepted');
check(!User::completePasswordReset($id,'New-verify-passphrase',$wrongToken), 'Wrong token cannot change password');
check(User::completePasswordReset($id,'New-verify-passphrase',$token), 'Correct token changes password');
check(!User::completePasswordReset($id,'Replay-passphrase',$token), 'Reset token cannot be reused');
User::requestPasswordReset($id);
$token = User::approvePasswordReset($id);
$db->exec("UPDATE users SET password_reset_expires_at=DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE id=".$id);
check(!User::resetTokenValid(User::find($id),$token), 'Expired reset token rejected');
check(!User::completePasswordReset($id,'Expired-passphrase',$token), 'Expired token cannot update database');
check(!security_password_valid('short') && !security_password_valid(str_repeat('x',73)) && security_password_valid('a long passphrase'), 'Password length and bcrypt byte boundary enforced');
$key='regression:'.bin2hex(random_bytes(12));
check(security_rate_limit($key,2) && security_rate_limit($key,2) && !security_rate_limit($key,2), 'Rate limit enforced across successive calls');
// Restore only the synthetic fixture password for HTTP checks.
$stmt=$db->prepare('UPDATE users SET password_hash=:hash WHERE id=:id');
$stmt->execute(['hash'=>password_hash('Verify-only-passphrase-2026',PASSWORD_DEFAULT),'id'=>$id]);
