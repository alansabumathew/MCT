<?php
declare(strict_types=1);

$root = dirname(__DIR__);
if (!is_file($root . '/vendor/autoload.php')) {
    http_response_code(500);
    exit('Dependencies missing. Run "composer install".');
}
require $root . '/vendor/autoload.php';

$GLOBALS['config'] = require $root . '/config.php';

function cfg(?string $key = null)
{
    $c = $GLOBALS['config'];
    return $key === null ? $c : ($c[$key] ?? null);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . cfg('db_path'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        pan TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL,
        amount REAL NOT NULL,
        cause_suggestion TEXT,
        screenshot_drive_id TEXT,
        screenshot_url TEXT,
        status TEXT NOT NULL DEFAULT 'pending',
        receipt_no TEXT,
        ip TEXT,
        created_at TEXT NOT NULL,
        approved_at TEXT,
        approved_by TEXT
    )");
    $pdo->exec('CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT NOT NULL, at INTEGER NOT NULL
    )');
    return $pdo;
}

function h($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** Fill {{placeholders}} in a template file with HTML-escaped values. */
function render(string $file, array $vars): string
{
    $html = file_get_contents(dirname(__DIR__) . '/templates/' . $file);
    foreach ($vars as $k => $v) {
        $html = str_replace('{{' . $k . '}}', h($v), $html);
    }
    return $html;
}

/** Indian financial year label for a date, e.g. 2026-27. */
function financial_year(DateTimeInterface $d): string
{
    $y = (int)$d->format('Y');
    $start = ((int)$d->format('n') >= 4) ? $y : $y - 1;
    return $start . '-' . substr((string)($start + 1), 2);
}
