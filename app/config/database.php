<?php
declare(strict_types=1);

$privateFile = getenv('FSR_DB_CONFIG_FILE') ?: dirname(BASE_PATH, 2) . '/fsr-private/database.php';
$private = is_file($privateFile) ? require $privateFile : [];
return [
    'host' => getenv('FSR_DB_HOST') ?: ($private['host'] ?? '127.0.0.1'),
    'port' => getenv('FSR_DB_PORT') ?: ($private['port'] ?? '3306'),
    'database' => getenv('FSR_DB_NAME') ?: ($private['database'] ?? 'fsr'),
    'username' => getenv('FSR_DB_USER') ?: ($private['username'] ?? 'fsr_app'),
    'password' => getenv('FSR_DB_PASSWORD') !== false ? getenv('FSR_DB_PASSWORD') : ($private['password'] ?? ''),
    'charset' => getenv('FSR_DB_CHARSET') ?: ($private['charset'] ?? 'utf8mb4'),
];
