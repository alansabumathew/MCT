<?php
declare(strict_types=1);

require __DIR__ . '/_ui.php';
require_admin();

$status = $_GET['status'] ?? '';
$q = trim((string)($_GET['q'] ?? ''));

$sql = 'SELECT * FROM donations WHERE 1=1';
$args = [];
if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
    $sql .= ' AND status = ?';
    $args[] = $status;
}
if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR receipt_no LIKE ?)';
    $like = '%' . $q . '%';
    array_push($args, $like, $like, $like, $like);
}
$sql .= ' ORDER BY id DESC LIMIT 500';
$s = db()->prepare($sql);
$s->execute($args);
$rows = $s->fetchAll();

$tot = db()->query("SELECT
    SUM(status='pending') p, SUM(status='approved') a, SUM(status='rejected') r,
    COALESCE(SUM(CASE WHEN status='approved' THEN amount END),0) amt FROM donations")->fetch();

admin_head('Donations');
?>
<div class="stats">
  <div class="card"><b><?= (int)$tot['p'] ?></b>Pending</div>
  <div class="card"><b><?= (int)$tot['a'] ?></b>Approved</div>
  <div class="card"><b><?= (int)$tot['r'] ?></b>Rejected</div>
  <div class="card"><b>&#8377;<?= number_format((float)$tot['amt'], 2) ?></b>Approved total</div>
</div>
<br>
<div class="card">
  <form method="get" style="margin-bottom:12px">
    <select name="status">
      <option value="">All</option>
      <?php foreach (['pending', 'approved', 'rejected'] as $o): ?>
        <option value="<?= $o ?>" <?= $status === $o ? 'selected' : '' ?>><?= ucfirst($o) ?></option>
      <?php endforeach; ?>
    </select>
    <input name="q" value="<?= h($q) ?>" placeholder="Search name / email / phone / receipt">
    <button type="submit">Filter</button>
  </form>
  <div class="table-scroll">
  <table>
    <tr><th>#</th><th>Date</th><th>Name</th><th>PAN</th><th>Amount</th><th>Status</th><th>Receipt</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= h(date('d M Y H:i', strtotime($r['created_at']))) ?></td>
        <td><?= h($r['name']) ?><br><small><?= h($r['email']) ?></small></td>
        <td><?= h(mask_pan($r['pan'])) ?></td>
        <td>&#8377;<?= number_format((float)$r['amount'], 2) ?></td>
        <td><span class="badge <?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
        <td><?= h($r['receipt_no'] ?? '-') ?></td>
        <td><a class="btn" href="view.php?id=<?= (int)$r['id'] ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="8">No donations found.</td></tr><?php endif; ?>
  </table>
  </div>
</div>
<?php admin_foot();
