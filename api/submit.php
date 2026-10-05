<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/mailer.php';
require __DIR__ . '/../lib/drive.php';

header('Content-Type: application/json');

function fail(int $code, string $msg): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'Method not allowed.');

// Honeypot: bots fill this; pretend success.
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$pdo = db();

$s = $pdo->prepare('SELECT COUNT(*) FROM donations WHERE ip = ? AND created_at > ?');
$s->execute([$ip, date('c', time() - 3600)]);
if ((int)$s->fetchColumn() >= (int)cfg('submit_limit_per_hour')) {
    fail(429, 'Too many submissions. Please try again later.');
}

$name = trim((string)($_POST['name'] ?? ''));
$pan = strtoupper(trim((string)($_POST['pan'] ?? '')));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$amount = (float)($_POST['amount'] ?? 0);
$cause = trim((string)($_POST['cause'] ?? ''));

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) fail(422, 'Please enter a valid name.');
if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) fail(422, 'Please enter a valid PAN.');
if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) fail(422, 'Please enter a valid 10-digit phone number.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) fail(422, 'Please enter a valid email.');
if ($amount <= 0 || $amount > 100000000) fail(422, 'Please enter a valid amount.');
$cause = mb_substr($cause, 0, 1000);

// Screenshot
$f = $_FILES['screenshot'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
    fail(422, 'Please upload the payment screenshot.');
}
if ($f['size'] > cfg('max_upload_bytes')) fail(422, 'Screenshot must be 5 MB or smaller.');
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
$exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
if (!isset($exts[$mime])) fail(422, 'Screenshot must be a JPG, PNG, WEBP or PDF file.');

try {
    $driveName = date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $exts[$mime];
    $up = drive_upload($f['tmp_name'], $driveName, $mime);
} catch (Throwable $e) {
    error_log('Drive upload failed: ' . $e->getMessage());
    fail(500, 'Could not save your screenshot. Please try again shortly.');
}

$pdo->prepare('INSERT INTO donations
    (name, pan, phone, email, amount, cause_suggestion, screenshot_drive_id, screenshot_url, status, ip, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending", ?, ?)')
    ->execute([$name, $pan, $phone, $email, $amount, $cause, $up['id'], $up['url'], $ip, date('c')]);

// Thank-you email. Failure here must not lose the submission.
try {
    $t = cfg('trust');
    $html = render('thankyou.html', [
        'name' => $name,
        'amount' => number_format($amount, 2),
        'trust_name' => $t['name'],
        'trust_email' => $t['email'],
    ]);
    send_mail($email, $name, 'Thank you for your donation - ' . $t['name'], $html);
} catch (Throwable $e) {
    error_log('Thank-you mail failed: ' . $e->getMessage());
}

echo json_encode(['success' => true]);
