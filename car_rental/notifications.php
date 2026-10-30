<?php
require 'config.php'; require_login(); $title = 'Notifications'; $uid = $_SESSION['user']['id'];
$st = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100"); $st->execute([$uid]); $rows = $st->fetchAll();
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);
require 'includes/header.php';
?>
<h2>Notifications</h2>
<div class="notifs">
<?php foreach ($rows as $n): ?>
  <div class="notif <?= $n['is_read'] ? '' : 'unread' ?>">
    <div><?= e($n['message']) ?><br><small><?= e($n['created_at']) ?></small></div>
    <?php if ($n['link']): ?><a class="btn small" href="<?= BASE . e($n['link']) ?>">View</a><?php endif; ?>
  </div>
<?php endforeach; if (!$rows) echo '<p class="muted">No notifications yet.</p>'; ?>
</div>
<?php require 'includes/footer.php'; ?>
