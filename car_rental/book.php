<?php
require 'config.php'; require_login(); $title = 'Book & pay';
if ($_SESSION['user']['role'] === 'admin') { flash('Admin accounts cannot book cars.', 'error'); header('Location: index.php'); exit; }
$uid = $_SESSION['user']['id']; $id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM cars WHERE id=? AND status='available'"); $st->execute([$id]); $car = $st->fetch();
if (!$car) { flash('Car not found or unavailable.', 'error'); header('Location: index.php'); exit; }
$me = $pdo->prepare("SELECT name, phone FROM users WHERE id=?"); $me->execute([$uid]); $me = $me->fetch();
$start = $_GET['start'] ?? ''; $end = $_GET['end'] ?? ''; $method = ''; $ref = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $start = $_POST['start'] ?? ''; $end = $_POST['end'] ?? '';
    $method = $_POST['method'] ?? ''; $ref = trim($_POST['ref'] ?? '');
    $clean = preg_replace('/\s+/', '', $ref);
    $d1 = valid_date($start); $d2 = valid_date($end);
    if (!$d1 || !$d2) $err = 'Choose valid dates.';
    elseif ($start < date('Y-m-d')) $err = 'Pick-up date cannot be in the past.';
    elseif ($end < $start) $err = 'Return date must be on or after pick-up date.';
    elseif (!isset(methods()[$method])) $err = 'Please choose a payment method.';
    elseif ($method === 'mtn' && !preg_match('/^(\+?250|0)7[89]\d{7}$/', $clean)) $err = 'Enter a valid MTN number (078... or 079...).';
    elseif ($method === 'airtel' && !preg_match('/^(\+?250|0)7[23]\d{7}$/', $clean)) $err = 'Enter a valid Airtel number (072... or 073...).';
    elseif ($method === 'bank' && strlen($ref) < 6) $err = 'Enter the bank transfer reference (at least 6 characters).';
    else {
        $days = $d1->diff($d2)->days + 1; $total = $days * $car['price_per_day']; $ok = false;
        try {
            $pdo->beginTransaction();
            if (!car_is_free($pdo, $id, $start, $end)) { $pdo->rollBack(); $err = 'Sorry, this car is already booked for those dates.'; }
            else {
                $pdo->prepare("INSERT INTO bookings (user_id,car_id,start_date,end_date,days,total_price) VALUES (?,?,?,?,?,?)")
                    ->execute([$uid, $id, $start, $end, $days, $total]);
                $bid = $pdo->lastInsertId(); $tx = 'TX' . strtoupper(bin2hex(random_bytes(4)));
                $pdo->prepare("INSERT INTO payments (booking_id,user_id,amount,method,account_ref,transaction_ref) VALUES (?,?,?,?,?,?)")
                    ->execute([$bid, $uid, $total, $method, $ref, $tx]);
                $pdo->commit(); $ok = true;
            }
        } catch (Exception $x) { if ($pdo->inTransaction()) $pdo->rollBack(); $err = 'Payment failed, please try again.'; }
        if ($ok) {
            $carName = $car['brand'] . ' ' . $car['model']; $ml = method_label($method);
            $custMsg = "Payment of " . money($total) . " received via $ml (Ref $tx). Booking #$bid: $carName, $start to $end. Waiting for admin approval.";
            notify($pdo, $uid, $custMsg, 'my_bookings.php');
            $smsTo = $me['phone'] ?: ($method !== 'bank' ? $clean : '');
            $sms = send_sms($pdo, $uid, $smsTo, 'DriveEasy: ' . $custMsg);
            notify_admins($pdo, "New paid booking #$bid: {$me['name']} booked $carName ($start to $end), " . money($total) . " paid via $ml.", 'admin/bookings.php', true);
            flash("Payment received ($tx) and booking #$bid created." . ($sms !== 'failed' ? ' A confirmation SMS was sent to your phone.' : '') . ' The admin has been notified.');
            header('Location: my_bookings.php'); exit;
        }
    }
}
$hints = [
  'bank'   => ['Bank transfer reference', 'Transfer the total to ' . BANK_NAME . ', account ' . BANK_ACCOUNT . ', then enter the transfer reference.'],
  'mtn'    => ['Your MTN MoMo number', 'Pay to MTN MoMo ' . MTN_NUMBER . '. Approve the request on your phone.'],
  'airtel' => ['Your Airtel Money number', 'Pay to Airtel Money ' . AIRTEL_NUMBER . '. Approve the request on your phone.'],
];
require 'includes/header.php';
?>
<div class="box wide"><h2>Book &amp; pay: <?= e($car['brand'] . ' ' . $car['model']) ?></h2>
<p class="muted"><?= money($car['price_per_day']) ?> per day · <?= e($car['transmission']) ?> · <?= e($car['seats']) ?> seats</p>
<?php if ($err) echo '<div class="alert error">' . e($err) . '</div>'; ?>
<form method="post" action="book.php?id=<?= $id ?>"><?= csrf_field() ?>
  <label>Pick-up date<input type="date" id="start" name="start" min="<?= date('Y-m-d') ?>" required value="<?= e($start) ?>"></label>
  <label>Return date<input type="date" id="end" name="end" min="<?= date('Y-m-d') ?>" required value="<?= e($end) ?>"></label>
  <p class="totalbox">Total: <b id="total">Select dates</b></p>
  <h3>Payment method</h3>
  <div class="methods">
  <?php foreach (methods() as $k => $lbl): ?>
    <label class="method"><input type="radio" name="method" value="<?= $k ?>" required <?= $method === $k ? 'checked' : '' ?>><span><?= e($lbl) ?></span></label>
  <?php endforeach; ?></div>
  <div id="payinfo" class="alert info">Choose a payment method above.</div>
  <label><span id="reflabel">Payment reference / number</span><input name="ref" required value="<?= e($ref) ?>"></label>
  <button class="btn">Pay &amp; confirm booking</button>
  <p class="muted"><small>Payment is required before the booking is created. You will get a notification and an SMS.</small></p>
</form></div>
<script>
const price = <?= (float)$car['price_per_day'] ?>, cur = <?= json_encode(CURRENCY) ?>, hints = <?= json_encode($hints) ?>;
function upd() {
  const s = document.getElementById('start').value, e = document.getElementById('end').value; let t = 'Select dates';
  if (s && e && e >= s) { const d = Math.round((new Date(e) - new Date(s)) / 864e5) + 1; t = d + ' day(s) = ' + cur + ' ' + (d * price).toLocaleString(); }
  document.getElementById('total').textContent = t;
}
function meth() {
  const m = document.querySelector('input[name=method]:checked'); if (!m) return;
  document.getElementById('reflabel').textContent = hints[m.value][0];
  document.getElementById('payinfo').textContent = hints[m.value][1];
}
document.getElementById('start').onchange = document.getElementById('end').onchange = upd;
document.querySelectorAll('input[name=method]').forEach(r => r.onchange = meth);
upd(); meth();
</script>
<?php require 'includes/footer.php'; ?>
