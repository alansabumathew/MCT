<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require_admin();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="donations-' . date('Ymd') . '.csv"');

// Neutralise spreadsheet formula injection from donor-supplied text.
function safe($v): string
{
    $v = (string)$v;
    return ($v !== '' && strpos('=+-@', $v[0]) !== false) ? "'" . $v : $v;
}

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['ID', 'Submitted', 'Name', 'PAN', 'Phone', 'Email', 'Amount', 'Cause suggestion', 'Status', 'Receipt No', 'Approved at', 'Approved by', 'Screenshot']);
foreach (db()->query('SELECT * FROM donations ORDER BY id') as $r) {
    fputcsv($out, [
        $r['id'], $r['created_at'], safe($r['name']), $r['pan'], $r['phone'], safe($r['email']), $r['amount'],
        safe($r['cause_suggestion']), $r['status'], $r['receipt_no'], $r['approved_at'], $r['approved_by'], $r['screenshot_url'],
    ]);
}
