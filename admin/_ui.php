<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/receipt.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

function admin_head(string $title, bool $nav = true): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>' . h($title) . ' | MCT Admin</title>
<style>
  body{font-family:Poppins,Arial,sans-serif;background:#f4f6f8;margin:0;color:#222}
  .bar{background:#102542;color:#fff;padding:12px 24px;display:flex;justify-content:space-between;align-items:center}
  .bar a{color:#fff;margin-left:16px;text-decoration:none;font-size:14px}
  .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
  .card{background:#fff;border:1px solid #dde2e7;border-radius:8px;padding:20px;margin-bottom:16px}
  table{width:100%;border-collapse:collapse;font-size:14px}
  th,td{text-align:left;padding:10px;border-bottom:1px solid #eee;vertical-align:top}
  th{background:#f4f9fa}
  .badge{padding:2px 10px;border-radius:12px;font-size:12px;color:#fff}
  .pending{background:#e69500}.approved{background:#1e8e3e}.rejected{background:#d93025}
  button,.btn{background:#068d9d;color:#fff;border:0;border-radius:4px;padding:8px 18px;cursor:pointer;font-size:14px;text-decoration:none;display:inline-block}
  button.danger{background:#d93025}
  input,select{padding:8px;border:1px solid #ccc;border-radius:4px;font-size:14px}
  .err{color:#d93025}.ok{color:#1e8e3e}
  .stats{display:flex;gap:16px;flex-wrap:wrap}.stats .card{flex:1;min-width:150px;margin:0}
  .stats b{display:block;font-size:22px}
  dl{display:grid;grid-template-columns:180px 1fr;gap:8px 12px}dt{color:#666}dd{margin:0}
  .table-scroll{overflow-x:auto}
</style></head><body>';
    if ($nav) {
        echo '<div class="bar"><strong>Mahima Charitable Trust &mdash; Admin</strong><div>
<a href="index.php">Donations</a><a href="export.php">Export CSV</a><a href="logout.php">Logout</a></div></div>';
    }
    echo '<div class="wrap">';
}

function admin_foot(): void
{
    echo '</div></body></html>';
}
