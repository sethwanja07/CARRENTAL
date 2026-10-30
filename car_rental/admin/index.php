<?php
require '../config.php'; require_admin(); $title = 'Admin dashboard';
$n = fn($sql) => $pdo->query($sql)->fetchColumn();
$stats = [
  'Cars' => $n("SELECT COUNT(*) FROM cars"),
  'Customers' => $n("SELECT COUNT(*) FROM users WHERE role='customer'"),
  'Pending bookings' => $n("SELECT COUNT(*) FROM bookings WHERE status='pending'"),
  'Active (approved)' => $n("SELECT COUNT(*) FROM bookings WHERE status='approved'"),
  'Payments received' => money($n("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")),
  'Refunds pending' => $n("SELECT COUNT(*) FROM payments WHERE status='refund_pending'"),
  'Unread notifications' => $n("SELECT COUNT(*) FROM notifications WHERE is_read=0 AND user_id=" . (int)$_SESSION['user']['id']),
];
require '../includes/header.php';
?>
<h2>Dashboard</h2>
<div class="stats"><?php foreach ($stats as $k => $v): ?><div class="stat"><b><?= e($v) ?></b><span><?= e($k) ?></span></div><?php endforeach; ?></div>
<p><a class="btn" href="bookings.php">Review bookings</a> <a class="btn" href="cars.php">Manage cars</a></p>
<?php require '../includes/footer.php'; ?>
