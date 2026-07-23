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
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/** Keep unexpected PHP failures user-visible and reportable instead of rendering a blank page. */
ob_start();

function render_system_error(\Throwable $error): never
{
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $description = $error->getMessage() . "\n" . $error->getFile() . ':' . $error->getLine();
    $payload = json_encode([
        'description' => $description,
        'pageUrl' => ($_SERVER['REQUEST_URI'] ?? ''),
        'browser' => ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'csrfToken' => csrf_token(),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $modal = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>System error</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><div class="modal d-block" tabindex="-1" role="dialog" aria-modal="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h1 class="modal-title fs-5">System Error</h1></div><div class="modal-body"><p>"An error occured: ' . e($description) . '"</p></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" onclick="history.back()">Don\'t Send Error Report</button><button class="btn btn-danger" type="button" id="sendError">Send Error to System Administrator</button></div></div></div></div><script>const errorReport=' . $payload . ';document.getElementById("sendError").addEventListener("click",async()=>{if(!confirm("Are you sure you want to send an error report?"))return;const body=new URLSearchParams({action:"error-report",csrf_token:errorReport.csrfToken,description:errorReport.description,page_url:errorReport.pageUrl,browser:errorReport.browser});const response=await fetch("index.php",{method:"POST",headers:{"X-Requested-With":"fetch","Content-Type":"application/x-www-form-urlencoded"},body,credentials:"same-origin"});if(response.ok){document.getElementById("sendError").textContent="Error report sent";document.getElementById("sendError").disabled=true;}else{alert("The error report could not be sent.");}});</script></body></html>';
    echo $modal;
    exit;
}

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (\Throwable $error): never {
    render_system_error($error);
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        render_system_error(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
    }
});

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

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

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(mixed $token): bool
{
    return is_string($token)
        && $token !== ''
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
