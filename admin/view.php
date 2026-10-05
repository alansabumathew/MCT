<?php
declare(strict_types=1);

require __DIR__ . '/_ui.php';
require_admin();

$s = db()->prepare('SELECT * FROM donations WHERE id = ?');
$s->execute([(int)($_GET['id'] ?? 0)]);
$d = $s->fetch();
if (!$d) {
    http_response_code(404);
    exit('Not found.');
}

admin_head('Donation #' . $d['id']);
if (!empty($_GET['msg'])) echo '<p class="ok">' . h($_GET['msg']) . '</p>';
if (!empty($_GET['err'])) echo '<p class="err">' . h($_GET['err']) . '</p>';
?>
<p><a href="index.php">&larr; Back</a></p>
<div class="card">
  <h2>Donation #<?= (int)$d['id'] ?> <span class="badge <?= h($d['status']) ?>"><?= h($d['status']) ?></span></h2>
  <dl>
    <dt>Name</dt><dd><?= h($d['name']) ?></dd>
    <dt>PAN</dt><dd><?= h($d['pan']) ?></dd>
    <dt>Phone</dt><dd><?= h($d['phone']) ?></dd>
    <dt>Email</dt><dd><?= h($d['email']) ?></dd>
    <dt>Amount</dt><dd>&#8377;<?= number_format((float)$d['amount'], 2) ?></dd>
    <dt>Cause suggestion</dt><dd><?= $d['cause_suggestion'] !== '' ? nl2br(h($d['cause_suggestion'])) : '-' ?></dd>
    <dt>Submitted</dt><dd><?= h(date('d M Y H:i', strtotime($d['created_at']))) ?></dd>
    <dt>Payment screenshot</dt><dd><a href="<?= h($d['screenshot_url']) ?>" target="_blank" rel="noopener">Open in Google Drive</a></dd>
    <?php if ($d['receipt_no']): ?>
      <dt>Receipt no.</dt><dd><?= h($d['receipt_no']) ?></dd>
      <dt>Approved</dt><dd><?= h(date('d M Y H:i', strtotime($d['approved_at']))) ?> by <?= h($d['approved_by']) ?></dd>
    <?php endif; ?>
  </dl>

  <?php if ($d['status'] === 'pending'): ?>
    <form method="post" action="approve.php" style="display:inline" onsubmit="return confirm('Approve and email the receipt to <?= h($d['email']) ?>?')">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
      <input type="hidden" name="action" value="approve">
      <button type="submit">Approve &amp; send receipt</button>
    </form>
    <form method="post" action="approve.php" style="display:inline" onsubmit="return confirm('Reject this donation?')">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
      <input type="hidden" name="action" value="reject">
      <button type="submit" class="danger">Reject</button>
    </form>
  <?php endif; ?>
</div>
<?php admin_foot();
