<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/mailer.php';
require __DIR__ . '/../lib/receipt.php';

$admin = require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
check_csrf();

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$pdo = db();

function back(int $id, string $key, string $msg): void
{
    header('Location: view.php?id=' . $id . '&' . $key . '=' . urlencode($msg));
    exit;
}

if ($action === 'reject') {
    $n = $pdo->prepare("UPDATE donations SET status='rejected' WHERE id = ? AND status = 'pending'");
    $n->execute([$id]);
    back($id, $n->rowCount() ? 'msg' : 'err', $n->rowCount() ? 'Donation rejected.' : 'Only pending donations can be rejected.');
}
if ($action !== 'approve') back($id, 'err', 'Unknown action.');

// Claim the donation and allocate the receipt number atomically.
$pdo->exec('BEGIN IMMEDIATE');
try {
    $s = $pdo->prepare('SELECT * FROM donations WHERE id = ?');
    $s->execute([$id]);
    $d = $s->fetch();
    if (!$d || $d['status'] !== 'pending') {
        $pdo->exec('ROLLBACK');
        back($id, 'err', 'This donation is not pending.');
    }

    $now = new DateTimeImmutable();
    $fy = financial_year($now);
    $c = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE receipt_no LIKE ?");
    $c->execute(['MCT/' . $fy . '/%']);
    $receiptNo = sprintf('MCT/%s/%04d', $fy, (int)$c->fetchColumn() + 1);

    $pdo->prepare("UPDATE donations SET status='approved', receipt_no=?, approved_at=?, approved_by=? WHERE id=?")
        ->execute([$receiptNo, $now->format('c'), $admin, $id]);
    $d['receipt_no'] = $receiptNo;
    $d['approved_at'] = $now->format('c');
    $pdo->exec('COMMIT');
} catch (Throwable $e) {
    $pdo->exec('ROLLBACK');
    error_log('Approve failed: ' . $e->getMessage());
    back($id, 'err', 'Could not approve. Please try again.');
}

// Email the receipt. If it fails, revert so the admin can retry (receipt number is released).
try {
    $pdf = receipt_pdf($d);
    $html = render('receipt_email.html', receipt_vars($d));
    $file = 'Receipt-' . str_replace('/', '-', $d['receipt_no']) . '.pdf';
    send_mail($d['email'], $d['name'], 'Your donation receipt ' . $d['receipt_no'] . ' - ' . cfg('trust')['name'], $html, ['data' => $pdf, 'name' => $file]);
} catch (Throwable $e) {
    error_log('Receipt mail failed: ' . $e->getMessage());
    $pdo->prepare("UPDATE donations SET status='pending', receipt_no=NULL, approved_at=NULL, approved_by=NULL WHERE id=?")->execute([$id]);
    back($id, 'err', 'Receipt email failed, donation left as pending. Check the mail settings and retry.');
}

back($id, 'msg', 'Approved. Receipt ' . $receiptNo . ' emailed to ' . $d['email'] . '.');
