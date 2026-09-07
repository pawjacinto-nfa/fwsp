<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');

$composerAutoloader = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoloader)) {
    require_once $composerAutoloader;
}

if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0775, true);
}

$sessionPath = DATA_PATH . '/sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}

$isPublicScheduleRequest = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
    && (
        ($_GET['page'] ?? '') === 'schedule-status'
        || preg_match('#/s/[A-Za-z0-9_-]{16}/?(?:\?|$)#', (string) ($_SERVER['REQUEST_URI'] ?? '')) === 1
    );

if ($isPublicScheduleRequest) {
    $_SESSION = [];
} else {
    session_save_path($sessionPath);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Keep unexpected PHP failures user-visible and reportable instead of rendering a blank page. */
ob_start();

function system_error_reference(): string
{
    try {
        $suffix = strtoupper(bin2hex(random_bytes(3)));
    } catch (\Throwable) {
        $suffix = strtoupper(substr(hash('sha256', uniqid('', true)), 0, 6));
    }

    return 'FSR-' . gmdate('Ymd-His') . '-' . $suffix;
}

function redact_error_details(string $details): string
{
    $details = str_replace("\0", '', $details);
    $details = preg_replace('/(?i)(password|passwd|secret|authorization|cookie|csrf(?:_token)?|access[_-]?token|api[_-]?key)(\s*[:=]\s*)([^\s&;,]+)/', '$1$2[REDACTED]', $details) ?? $details;
    $details = preg_replace('/(?i)(Bearer\s+)[A-Za-z0-9._~+\/=-]+/', '$1[REDACTED]', $details) ?? $details;

    return $details;
}

function truncate_error_details(string $details, int $maximumBytes): string
{
    if (strlen($details) <= $maximumBytes) {
        return $details;
    }

    return function_exists('mb_strcut')
        ? mb_strcut($details, 0, $maximumBytes, 'UTF-8')
        : substr($details, 0, $maximumBytes);
}

function throwable_error_report(\Throwable $error, string $reference): string
{
    $lines = [
        'Error reference: ' . $reference,
        'Occurred at: ' . date(DATE_ATOM),
        'Source: Server / PHP',
        'Error type: ' . $error::class,
        'Message: ' . ($error->getMessage() !== '' ? $error->getMessage() : '(no exception message)'),
        'Error code: ' . (string) $error->getCode(),
        'Request method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown'),
        'Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'Unknown'),
        'Signed-in user: ' . (!empty($_SESSION['user_id'])
            ? '#' . (int) $_SESSION['user_id'] . ' - ' . ($_SESSION['user'] ?? 'Unknown name') . ' (' . ($_SESSION['role'] ?? 'Unknown role') . ')'
            : 'Anonymous'),
        'PHP version: ' . PHP_VERSION,
        'Source location: ' . $error->getFile() . ':' . $error->getLine(),
        '',
        'Stack trace:',
        $error->getTraceAsString() !== '' ? $error->getTraceAsString() : '(no stack trace available)',
    ];

    $previous = $error->getPrevious();
    $depth = 0;
    while ($previous !== null && $depth < 5) {
        $lines[] = '';
        $lines[] = sprintf(
            'Caused by: %s: %s at %s:%d',
            $previous::class,
            $previous->getMessage() !== '' ? $previous->getMessage() : '(no exception message)',
            $previous->getFile(),
            $previous->getLine()
        );
        $lines[] = $previous->getTraceAsString();
        $previous = $previous->getPrevious();
        $depth++;
    }

    return truncate_error_details(redact_error_details(implode("\n", $lines)), 54000);
}

function render_system_error(\Throwable $error): never
{
    http_response_code(500);
    $reference = system_error_reference();
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-FSR-Error-Reference: ' . $reference);
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $description = throwable_error_report($error, $reference);
    error_log(str_replace("\n", ' | ', $description));
    $payload = json_encode([
        'reference' => $reference,
        'description' => $description,
        'pageUrl' => redact_error_details((string) ($_SERVER['REQUEST_URI'] ?? '')),
        'browser' => redact_error_details((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')),
        'csrfToken' => csrf_token(),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
    $summary = redact_error_details($error->getMessage() !== '' ? $error->getMessage() : 'The application encountered an unexpected server error.');
    $modal = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>System error</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><style>pre{white-space:pre-wrap;overflow-wrap:anywhere;max-height:45vh;overflow:auto;background:#f8f9fa;padding:1rem;border-radius:.5rem;font-size:.8rem}</style></head><body><div class="modal d-block" tabindex="-1" role="dialog" aria-modal="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-header"><h1 class="modal-title fs-5">System Error</h1></div><div class="modal-body"><p><strong>The request could not be completed.</strong> ' . e($summary) . '</p><p class="mb-2">Error reference: <strong>' . e($reference) . '</strong>. You can send the complete diagnostic details below to the System Administrator.</p><details><summary>Technical details</summary><pre data-server-error-details>' . e($description) . '</pre></details></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" onclick="history.back()">Don\'t Send Error Report</button><button class="btn btn-danger" type="button" id="sendError">Send Error to System Administrator</button></div></div></div></div><script>const errorReport=' . $payload . ';document.getElementById("sendError").addEventListener("click",async()=>{if(!confirm("Send this complete diagnostic report to the System Administrator?"))return;const button=document.getElementById("sendError");button.disabled=true;button.textContent="Sending error report...";try{const body=new URLSearchParams({action:"error-report",csrf_token:errorReport.csrfToken,report_id:errorReport.reference,description:errorReport.description,page_url:errorReport.pageUrl,browser:errorReport.browser});const response=await fetch("index.php",{method:"POST",headers:{"X-Requested-With":"fetch","Content-Type":"application/x-www-form-urlencoded"},body,credentials:"same-origin"});const result=await response.json().catch(()=>({}));if(!response.ok)throw new Error(result.message||"The server rejected the error report.");button.textContent="Error report sent (Ticket #"+result.ticket_id+")";}catch(error){button.disabled=false;button.textContent="Retry sending error report";alert(error.message||"The error report could not be sent.");}});</script></body></html>';
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

/** Canonical public origin used in printed QR codes and client status links. */
function public_app_url(): string
{
    $configured = trim((string) (getenv('FSR_PUBLIC_BASE_URL') ?: ''));
    return rtrim($configured !== '' ? $configured : 'https://sandbox.nfa.gov.ph/fsr', '/');
}

function delivery_schedule_public_url(string $token): string
{
    return public_app_url() . '/s/' . rawurlencode($token);
}

function app_base_path(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/fsr/index.php'));
    $directory = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return $directory === '.' ? '' : $directory;
}
