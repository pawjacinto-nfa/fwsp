<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');

if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0775, true);
}

$sessionPath = DATA_PATH . '/sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}

session_save_path($sessionPath);
session_start();

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
