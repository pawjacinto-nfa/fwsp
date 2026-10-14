<?php
declare(strict_types=1);
namespace App\Support;

use PHPMailer\PHPMailer\PHPMailer;

final class ResetMailer
{
    public static function config(): array
    {
        $file = getenv('FSR_MAIL_CONFIG_FILE') ?: dirname(BASE_PATH, 2) . '/fsr-private/mail.php';
        $config = is_file($file) ? require $file : [];
        foreach (['host', 'port', 'username', 'password', 'encryption', 'from', 'base_url'] as $key) {
            $value = getenv('FSR_MAIL_' . strtoupper($key));
            if ($value !== false) $config[$key] = $value;
        }
        return $config;
    }

    public static function content(string $name, string $code, string $link, bool $test = false): array
    {
        $name = trim($name) !== '' ? trim($name) : 'User';
        ob_start();
        require BASE_PATH . '/app/Views/emails/password-reset.php';
        $html = (string) ob_get_clean();
        $upperName = function_exists('mb_strtoupper') ? mb_strtoupper($name, 'UTF-8') : strtoupper($name);
        $plain = "HI, {$upperName}\n\nYour password reset confirmation code is: {$code}\n\nOpen this link and enter your code:\n{$link}\n\nThe code and link expire in 15 minutes. You can also enter the code with your employee number on the password reset screen.\n\nIf you did not request this, ignore this email. Your password has not changed.\n\nYours,\nFSR System Administrator";
        if ($test) $plain .= "\n\nDelivery test only. This sample code and link cannot reset an account.";
        return ['html'=>$html, 'text'=>$plain];
    }

    public static function send(string $email, string $code, string $linkToken, bool $test = false, string $name = 'User'): void
    {
        $config = self::config();
        foreach (['host', 'username', 'password', 'from', 'base_url'] as $key) {
            if (empty($config[$key])) throw new \RuntimeException('Email delivery is not configured: ' . $key);
        }
        $url = rtrim($config['base_url'], '/');
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
            || isset($parts['query']) || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])
            || str_contains($url, '?') || str_contains($url, '#')) {
            throw new \RuntimeException('Configure an HTTPS application base URL.');
        }
        $encryption = $config['encryption'] ?? 'tls';
        if (!in_array($encryption, ['tls', 'ssl'], true)) throw new \RuntimeException('SMTP requires TLS.');
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = (int) ($config['port'] ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $encryption;
        $mail->Timeout = 15;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from'], 'Farmer-Seller Registry');
        $mail->addAddress($email);
        $mail->Subject = 'NFA Farmer Seller Registry: Password Reset Request';
        $link = $url . '/index.php?password_reset_link=' . rawurlencode($linkToken);
        $content = self::content($name, $code, $link, $test);
        $mail->isHTML(true);
        $mail->Body = $content['html'];
        $mail->AltBody = $content['text'];
        $mail->addEmbeddedImage(BASE_PATH . '/assets/images/email/reset-header.png', 'fsr-reset-header', 'header.png');
        $mail->addEmbeddedImage(BASE_PATH . '/assets/images/email/reset-footer.png', 'fsr-reset-footer', 'footer.png');
        $mail->send();
    }
}
