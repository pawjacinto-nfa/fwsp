<?php
declare(strict_types=1);
namespace App\Support;

// This transport exists only in explicitly isolated regression test processes.
if (getenv('FSR_DB_NAME') !== 'fsr_security_verify_20261002'
    || getenv('FSR_DB_PORT') !== '13306') {
    throw new \RuntimeException('Reset mail fixture requires the isolated test database.');
}
final class ResetMailer
{
    public static array $last = [];
    public static bool $fail = false;
    public static function config(): array
    {
        return array_fill_keys(['host','username','password','from','base_url'], 'fixture');
    }
    public static function send(string $email, string $code, string $link, bool $test = false, string $name = 'User'): void
    {
        if (self::$fail) throw new \RuntimeException('Simulated mail failure');
        self::$last = compact('email', 'code', 'link');
        $capture = getenv('FSR_TEST_MAIL_CAPTURE');
        if ($capture) file_put_contents($capture, json_encode(self::$last));
    }
}
