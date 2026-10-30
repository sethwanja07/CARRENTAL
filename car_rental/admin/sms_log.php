<?php
require '../config.php'; require_admin(); $title = 'SMS log';
$rows = $pdo->query("SELECT * FROM sms_log ORDER BY id DESC LIMIT 100")->fetchAll();
require '../includes/header.php';
?>
<h2>SMS log</h2>
<p class="muted">SMS gateway: <b><?= SMS_ENABLED && SMS_API_KEY !== '' ? 'ON (Africa\'s Talking)' : 'OFF - demo mode (messages are only recorded here)' ?></b>. Change this in config.php.</p>
<div class="scroll"><table><tr><th>Time</th><th>Phone</th><th>Message</th><th>Status</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['created_at']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['message']) ?></td>
<td><span class="st <?= $r['status'] === 'sent' ? 'approved' : ($r['status'] === 'failed' ? 'rejected' : 'pending') ?>"><?= e($r['status']) ?></span><br><small><?= e($r['response']) ?></small></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="4">No SMS yet.</td></tr>'; ?></table></div>
<?php require '../includes/footer.php'; ?>
