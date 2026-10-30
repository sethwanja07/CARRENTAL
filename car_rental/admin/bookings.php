<?php
require '../config.php'; require_admin(); $title = 'All bookings';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $map = ['approve' => ['approved', 'pending'], 'reject' => ['rejected', 'pending'], 'return' => ['returned', 'approved']];
    $a = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    if (isset($map[$a])) {
        $s = $pdo->prepare("SELECT b.*, c.brand, c.model, u.phone FROM bookings b JOIN cars c ON c.id=b.car_id JOIN users u ON u.id=b.user_id WHERE b.id=?");
        $s->execute([$id]); $b = $s->fetch();
        if ($b && $b['status'] === $map[$a][1]) {
            $pdo->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$map[$a][0], $id]);
            if ($a === 'reject') $pdo->prepare("UPDATE payments SET status='refund_pending' WHERE booking_id=? AND status='paid'")->execute([$id]);
            $car = $b['brand'] . ' ' . $b['model'];
            $msgs = [
              'approve' => "Your booking #$id for $car ({$b['start_date']} to {$b['end_date']}) is APPROVED. Please pick up the car on time.",
              'reject'  => "Your booking #$id for $car was rejected. Your payment will be refunded.",
              'return'  => "Thank you! Booking #$id ($car) is marked as returned."];
            notify($pdo, $b['user_id'], $msgs[$a], 'my_bookings.php');
            send_sms($pdo, $b['user_id'], $b['phone'], 'DriveEasy: ' . $msgs[$a]);
            flash('Booking updated. The customer was notified by app and SMS.');
        }
    }
    header('Location: bookings.php'); exit;
}
$rows = $pdo->query("SELECT b.*, u.name uname, u.phone, c.brand, c.model, c.plate, p.method, p.transaction_ref, p.status pstatus
  FROM bookings b JOIN users u ON u.id=b.user_id JOIN cars c ON c.id=b.car_id LEFT JOIN payments p ON p.booking_id=b.id ORDER BY b.id DESC")->fetchAll();
require '../includes/header.php';
function act($id, $action, $label, $cls = '') {
    return '<form method="post" style="display:inline">' . csrf_field() . '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="action" value="' . $action . '"><button class="btn small ' . $cls . '">' . $label . '</button></form> ';
}
?>
<h2>All bookings</h2>
<div class="scroll"><table><tr><th>#</th><th>Customer</th><th>Car</th><th>Dates</th><th>Payment</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= $r['id'] ?></td><td><?= e($r['uname']) ?><br><small><?= e($r['phone']) ?></small></td>
<td><?= e($r['brand'] . ' ' . $r['model']) ?><br><small><?= e($r['plate']) ?></small></td>
<td><?= e($r['start_date']) ?> → <?= e($r['end_date']) ?> (<?= $r['days'] ?>d)</td>
<td><?= money($r['total_price']) ?><br><?= $r['method'] ? '<small>' . e(method_label($r['method'])) . ' · ' . e($r['transaction_ref']) . '</small> <span class="st ' . e($r['pstatus']) . '">' . e(str_replace('_', ' ', $r['pstatus'])) . '</span>' : '<small>unpaid</small>' ?></td>
<td><span class="st <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
<td><?php
 if ($r['status'] === 'pending') echo act($r['id'], 'approve', 'Approve') . act($r['id'], 'reject', 'Reject', 'danger');
 if ($r['status'] === 'approved') echo act($r['id'], 'return', 'Mark returned');
?></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="7">No bookings.</td></tr>'; ?></table></div>
<?php require '../includes/footer.php'; ?>
