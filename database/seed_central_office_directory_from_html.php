<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/app/config/database.php';
$source = dirname(__DIR__) . '/directory-co.txt';

if (!is_file($source)) {
    fwrite(STDERR, "directory-co.txt was not found.\n");
    exit(1);
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);
$db = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function directoryCleanText(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\xC2\xA0", 'Â'], ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim((string) $text);
}

function directoryDepartmentName(string $text): string
{
    $text = directoryCleanText($text);
    $text = preg_replace('/\s*-\s*\d+(?:st|nd|rd|th)?\s*Floor.*$/iu', '', $text);
    $text = preg_replace('/\s*-\s*NFA\s*(?:Building|Bldg\.).*$/iu', '', (string) $text);
    $text = preg_replace('/\s*\([^)]*\)\s*$/u', '', (string) $text);

    return trim((string) $text, " \t\n\r\0\x0B.-");
}

function directoryDivisionName(string $text): ?string
{
    $text = directoryCleanText($text);

    if (preg_match('/\bICTSD\b/i', $text)) {
        return 'Information and Communications Technology Services Division';
    }

    if (!preg_match('/\b(?:Division|Div\.?)(?:\b|$)/i', $text)) {
        return null;
    }

    $text = preg_replace('/\bDiv\.?(?:\b|$)/i', 'Division', $text);
    $parts = preg_split('/,/', (string) $text);
    $candidate = trim((string) end($parts));

    if (!preg_match('/\bDivision\b/i', $candidate)) {
        $candidate = (string) $text;
    }

    $candidate = preg_replace(
        '/^(Chief|Officer[- ]?In[- ]?Charge|Acting|OIC|Planning Officer V|Budget Officer V|Internal Auditor V|Chief Administrative Officer|Chief Accountant|Accountant IV|Attorney VI|Administrative Officer V|Engineer V|Information Officer V|Statistician V|Division Manager)\b\s*,?\s*/iu',
        '',
        $candidate
    );
    $candidate = preg_replace('/^\/Chief\s*,?\s*/iu', '', (string) $candidate);
    $candidate = trim((string) $candidate, " \t\n\r\0\x0B,.-");

    if ($candidate === '' || preg_match('/\bDepartment Manager\b/i', $candidate)) {
        return null;
    }

    return $candidate;
}

$html = file_get_contents($source);
$html = preg_replace('/\[\/su_spoiler\].*$/s', '', (string) $html);

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
$xpath = new DOMXPath($dom);

$directory = [];
$currentDepartment = null;

foreach ($xpath->query('//tr') as $row) {
    $cells = $xpath->query('./td', $row);
    if ($cells->length === 0) {
        continue;
    }

    $firstCell = $cells->item(0);
    $colspan = $firstCell->attributes?->getNamedItem('colspan')?->nodeValue ?? '';
    $style = $firstCell->attributes?->getNamedItem('style')?->nodeValue ?? '';

    if ((int) $colspan >= 4 && stripos($style, 'background-color') !== false) {
        $currentDepartment = directoryDepartmentName($firstCell->textContent);
        if ($currentDepartment !== '') {
            $directory[$currentDepartment] ??= [];
        }
        continue;
    }

    if ($currentDepartment === null || $cells->length < 2) {
        continue;
    }

    $division = directoryDivisionName($cells->item(1)->textContent);
    if ($division !== null) {
        $directory[$currentDepartment][$division] = true;
    }
}

$db->exec("
    CREATE TABLE IF NOT EXISTS central_departments (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(180) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$db->exec("
    CREATE TABLE IF NOT EXISTS central_divisions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        department_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(180) NOT NULL,
        UNIQUE KEY central_division_unique (department_id, name),
        FOREIGN KEY (department_id) REFERENCES central_departments(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$db->exec("
    CREATE TABLE IF NOT EXISTS central_units (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        division_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(180) NOT NULL,
        UNIQUE KEY central_unit_unique (division_id, name),
        FOREIGN KEY (division_id) REFERENCES central_divisions(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$departmentStmt = $db->prepare('INSERT IGNORE INTO central_departments (name) VALUES (:name)');
$selectDepartmentStmt = $db->prepare('SELECT id FROM central_departments WHERE name = :name LIMIT 1');
$divisionStmt = $db->prepare('INSERT IGNORE INTO central_divisions (department_id, name) VALUES (:department_id, :name)');

$db->beginTransaction();
try {
    $departmentCount = 0;
    $divisionCount = 0;

    foreach ($directory as $department => $divisions) {
        $departmentStmt->execute(['name' => $department]);
        $selectDepartmentStmt->execute(['name' => $department]);
        $departmentId = (int) $selectDepartmentStmt->fetchColumn();
        $departmentCount++;

        foreach (array_keys($divisions) as $division) {
            $divisionStmt->execute([
                'department_id' => $departmentId,
                'name' => $division,
            ]);
            $divisionCount++;
        }
    }

    $db->commit();
    printf(
        "Imported %d central office departments/offices and %d divisions from directory-co.txt.\n",
        $departmentCount,
        $divisionCount
    );
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
