<?php
declare(strict_types=1);

return [
    'host' => getenv('FWSP_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('FWSP_DB_PORT') ?: '3306',
    'database' => getenv('FWSP_DB_NAME') ?: 'fwsp',
    'username' => getenv('FWSP_DB_USER') ?: 'root',
    'password' => getenv('FWSP_DB_PASSWORD') ?: '',
    'charset' => getenv('FWSP_DB_CHARSET') ?: 'utf8mb4',
];
