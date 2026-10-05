<?php
declare(strict_types=1);

function security_password_valid(string $password): bool
{
    // PASSWORD_DEFAULT currently uses bcrypt; reject silent truncation beyond 72 bytes.
    return strlen($password) >= 12 && strlen($password) <= 72 && !str_contains($password, "\0");
}

/** A server-side bucket survives cookie changes; flock serializes concurrent attempts. */
function security_rate_limit(string $key, int $limit, int $window = 900): bool
{
    $directory = DATA_PATH . '/rate-limits';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot initialize request protection.');
    }
    $file = fopen($directory . '/' . hash('sha256', $key), 'c+');
    if (!$file || !flock($file, LOCK_EX)) {
        throw new RuntimeException('Cannot lock request protection.');
    }
    try {
        $state = json_decode(stream_get_contents($file), true);
        $now = time();
        if (!is_array($state) || (int) ($state['expires'] ?? 0) <= $now) {
            $state = ['expires' => $now + $window, 'count' => 0];
        }
        $allowed = ++$state['count'] <= $limit;
        rewind($file);
        ftruncate($file, 0);
        fwrite($file, json_encode($state, JSON_THROW_ON_ERROR));
        fflush($file);
        return $allowed;
    } finally {
        flock($file, LOCK_UN);
        fclose($file);
    }
}
