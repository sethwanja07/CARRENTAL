<?php
require 'config.php'; require_login(); $title = 'My bookings'; $uid = $_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $bid = (int)$_POST['id'];
    $s = $pdo->prepare("SELECT b.*, c.brand, c.model FROM bookings b JOIN cars c ON c.id=b.car_id WHERE b.id=? AND b.user_id=? AND b.status IN ('pending','approved')");
    $s->execute([$bid, $uid]); $b = $s->fetch();
    if ($b) {
        $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$bid]);
        $pdo->prepare("UPDATE payments SET status='refund_pending' WHERE booking_id=? AND status='paid'")->execute([$bid]);
        notify($pdo, $uid, "You cancelled booking #$bid ({$b['brand']} {$b['model']}). Your refund is being processed.", 'my_bookings.php');
        notify_admins($pdo, "{$_SESSION['user']['name']} cancelled booking #$bid ({$b['brand']} {$b['model']}). Refund pending.", 'admin/bookings.php');
        flash('Booking cancelled. Your payment will be refunded.');
    }
    header('Location: my_bookings.php'); exit;
}
$st = $pdo->prepare("SELECT b.*, c.brand, c.model, c.plate, p.method, p.transaction_ref, p.status pstatus
  FROM bookings b JOIN cars c ON c.id=b.car_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.user_id=? ORDER BY b.id DESC");
$st->execute([$uid]); $rows = $st->fetchAll();
require 'includes/header.php';
?>
<h2>My bookings</h2>
<div class="scroll"><table><tr><th>#</th><th>Car</th><th>Dates</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= $r['id'] ?></td><td><?= e($r['brand'] . ' ' . $r['model']) ?><br><small><?= e($r['plate']) ?></small></td>
<td><?= e($r['start_date']) ?> → <?= e($r['end_date']) ?><br><small><?= $r['days'] ?> day(s)</small></td><td><?= money($r['total_price']) ?></td>
<td><?= $r['method'] ? e(method_label($r['method'])) . '<br><small>' . e($r['transaction_ref']) . '</small> <span class="st ' . e($r['pstatus']) . '">' . e(str_replace('_', ' ', $r['pstatus'])) . '</span>' : '-' ?></td>
<td><span class="st <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
<td><?php if (in_array($r['status'], ['pending', 'approved'])): ?>
<form method="post" onsubmit="return confirm('Cancel this booking?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn small danger">Cancel</button></form><?php endif; ?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="7">No bookings yet.</td></tr>'; ?></table></div>
<?php require 'includes/footer.php'; ?>
