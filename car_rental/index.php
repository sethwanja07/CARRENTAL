<?php
require 'config.php';
$title = 'Available cars';
$cat = $_GET['category'] ?? ''; $q = trim($_GET['q'] ?? '');
$start = $_GET['start'] ?? ''; $end = $_GET['end'] ?? '';
$sql = "SELECT * FROM cars WHERE status='available'"; $p = [];
if ($cat !== '') { $sql .= " AND category=?"; $p[] = $cat; }
if ($q !== '')   { $sql .= " AND (brand LIKE ? OR model LIKE ?)"; $p[] = "%$q%"; $p[] = "%$q%"; }
$dates = valid_date($start) && valid_date($end) && $start <= $end && $start >= date('Y-m-d');
if ($dates) {
    $sql .= " AND id NOT IN (SELECT car_id FROM bookings WHERE status IN ('pending','approved') AND start_date<=? AND end_date>=?)";
    $p[] = $end; $p[] = $start;
}
$sql .= " ORDER BY price_per_day";
$st = $pdo->prepare($sql); $st->execute($p); $cars = $st->fetchAll();
require 'includes/header.php';
?>
<section class="hero"><h1>Rent the right car, in minutes</h1><p>Search by dates to see only the cars that are free.</p></section>
<form class="filters" method="get">
  <input name="q" placeholder="Brand or model" value="<?= e($q) ?>">
  <select name="category"><option value="">All categories</option>
    <?php foreach (['Economy','Sedan','SUV','Van','Luxury'] as $c): ?>
      <option <?= $cat === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select>
  <input type="date" name="start" min="<?= date('Y-m-d') ?>" value="<?= e($start) ?>">
  <input type="date" name="end" min="<?= date('Y-m-d') ?>" value="<?= e($end) ?>">
  <button class="btn">Search</button>
</form>
<div class="grid">
<?php foreach ($cars as $c): ?>
  <div class="card"><?= car_img($c) ?>
    <div class="body"><span class="tag"><?= e($c['category']) ?></span>
      <h3><?= e($c['brand'] . ' ' . $c['model']) ?> <small><?= e($c['year']) ?></small></h3>
      <p class="muted"><?= e($c['transmission']) ?> · <?= e($c['fuel']) ?> · <?= e($c['seats']) ?> seats</p>
      <p class="price"><?= money($c['price_per_day']) ?> <small>/ day</small></p>
      <a class="btn" href="book.php?id=<?= $c['id'] ?><?= $dates ? '&start=' . e($start) . '&end=' . e($end) : '' ?>">Book now</a>
    </div></div>
<?php endforeach; if (!$cars) echo '<p>No cars match your search.</p>'; ?>
</div>
<?php require 'includes/footer.php'; ?>
