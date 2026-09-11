<?php
declare(strict_types=1);

return [
    'host' => getenv('FSR_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('FSR_DB_PORT') ?: '3306',
    'database' => getenv('FSR_DB_NAME') ?: 'fsr',
    'username' => getenv('FSR_DB_USER') ?: 'root',
    'password' => getenv('FSR_DB_PASSWORD') ?: '',
    'charset' => getenv('FSR_DB_CHARSET') ?: 'utf8mb4',
];
