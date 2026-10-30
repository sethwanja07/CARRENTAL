<?php
$u = $_SESSION['user'] ?? null; $unread = 0;
if ($u) { $s = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0"); $s->execute([$u['id']]); $unread = (int)$s->fetchColumn(); }
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'DriveEasy Car Rental') ?></title>
<link rel="icon" type="image/png" href="<?= BASE ?>assets/logo.png">
<link rel="stylesheet" href="<?= BASE ?>assets/style.css"></head><body>
<header><div class="wrap nav">
  <a class="brand" href="<?= BASE ?>index.php"><img src="<?= BASE ?>assets/logo.png" alt="DriveEasy logo" width="38" height="38"><span>Drive<b>Easy</b></span></a>
  <nav>
    <a href="<?= BASE ?>index.php">Cars</a>
    <?php if ($u): ?>
      <?php if ($u['role'] === 'admin'): ?>
        <a href="<?= BASE ?>admin/index.php">Dashboard</a>
        <a href="<?= BASE ?>admin/cars.php"> Manage Cars</a>
        <a href="<?= BASE ?>admin/bookings.php">Bookings</a>
        <a href="<?= BASE ?>admin/sms_log.php">SMS</a>
      <?php else: ?>
        <a href="<?= BASE ?>my_bookings.php">My bookings</a>
      <?php endif; ?>
      <a class="bell" href="<?= BASE ?>notifications.php" title="Notifications">🔔<?php if ($unread): ?><em><?= $unread ?></em><?php endif; ?></a>
      <a href="<?= BASE ?>logout.php">Logout (<?= e($u['name']) ?>)</a>
    <?php else: ?>
      <a href="<?= BASE ?>login.php">Login</a>
      <a class="btn small" href="<?= BASE ?>register.php">Register</a>
    <?php endif; ?>
  </nav></div></header>
<main class="wrap">
<?php if (!empty($_SESSION['flash'])): [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert <?= e($t) ?>"><?= e($m) ?></div>
<?php endif; ?>
