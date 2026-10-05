<?php
declare(strict_types=1);

function admin_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']),
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function require_admin(): string
{
    admin_session();
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['admin'];
}

function csrf_token(): string
{
    admin_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function check_csrf(): void
{
    admin_session();
    $t = $_POST['csrf'] ?? '';
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        exit('Invalid request token.');
    }
}

/** True if this IP has made 5+ failed logins in the last 15 minutes. */
function login_blocked(string $ip): bool
{
    $s = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND at > ?');
    $s->execute([$ip, time() - 900]);
    return (int)$s->fetchColumn() >= 5;
}

function record_failed_login(string $ip): void
{
    db()->prepare('INSERT INTO login_attempts (ip, at) VALUES (?, ?)')->execute([$ip, time()]);
}
