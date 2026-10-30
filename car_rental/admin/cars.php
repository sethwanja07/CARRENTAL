<?php
require '../config.php'; require_admin(); $title = 'Manage cars'; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) {
        try { $pdo->prepare("DELETE FROM cars WHERE id=?")->execute([(int)$_POST['delete']]); flash('Car deleted.'); }
        catch (PDOException $x) { flash('Cannot delete: this car has bookings. Set it to maintenance instead.', 'error'); }
        header('Location: cars.php'); exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $f = [trim($_POST['brand']), trim($_POST['model']), (int)$_POST['year'], strtoupper(trim($_POST['plate'])),
          $_POST['category'], $_POST['transmission'], $_POST['fuel'], (int)$_POST['seats'], (float)$_POST['price_per_day'], $_POST['status']];
    $image = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) || $_FILES['image']['size'] > 2 * 1024 * 1024) $err = 'Image must be jpg/png/webp under 2MB.';
        else { $image = bin2hex(random_bytes(8)) . '.' . $ext; move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $image); }
    }
    if (!$err) {
        try {
            if ($id) {
                $sql = "UPDATE cars SET brand=?,model=?,year=?,plate=?,category=?,transmission=?,fuel=?,seats=?,price_per_day=?,status=?" . ($image ? ",image=?" : "") . " WHERE id=?";
                if ($image) $f[] = $image; $f[] = $id;
            } else {
                $sql = "INSERT INTO cars (brand,model,year,plate,category,transmission,fuel,seats,price_per_day,status,image) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
                $f[] = $image;
            }
            $pdo->prepare($sql)->execute($f);
            flash('Car saved.'); header('Location: cars.php'); exit;
        } catch (PDOException $x) { $err = 'Could not save (is the plate number already used?).'; }
    }
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM cars WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$cars = $pdo->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll();
$v = fn($k, $d = '') => e($edit[$k] ?? $d);
function opts($list, $cur) { foreach ($list as $o) echo '<option' . ($o === $cur ? ' selected' : '') . '>' . $o . '</option>'; }
require '../includes/header.php';
?>
<h2><?= $edit ? 'Edit car' : 'Add a car' ?></h2>
<?php if ($err) echo '<div class="alert error">' . e($err) . '</div>'; ?>
<form method="post" enctype="multipart/form-data" class="formgrid"><?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
  <label>Brand<input name="brand" required value="<?= $v('brand') ?>"></label>
  <label>Model<input name="model" required value="<?= $v('model') ?>"></label>
  <label>Year<input type="number" name="year" min="1990" max="2100" required value="<?= $v('year', date('Y')) ?>"></label>
  <label>Plate<input name="plate" required value="<?= $v('plate') ?>"></label>
  <label>Category<select name="category"><?php opts(['Economy','Sedan','SUV','Van','Luxury'], $edit['category'] ?? '') ?></select></label>
  <label>Transmission<select name="transmission"><?php opts(['Manual','Automatic'], $edit['transmission'] ?? '') ?></select></label>
  <label>Fuel<select name="fuel"><?php opts(['Petrol','Diesel','Hybrid','Electric'], $edit['fuel'] ?? '') ?></select></label>
  <label>Seats<input type="number" name="seats" min="1" max="60" required value="<?= $v('seats', 5) ?>"></label>
  <label>Price / day<input type="number" step="500" name="price_per_day" required value="<?= $v('price_per_day') ?>"></label>
  <label>Status<select name="status"><?php opts(['available','maintenance'], $edit['status'] ?? '') ?></select></label>
  <label>Photo (optional)<input type="file" name="image" accept="image/*"></label>
  <div><button class="btn">Save car</button> <?php if ($edit) echo '<a class="btn small" href="cars.php">Cancel</a>'; ?></div>
</form>
<h2>Fleet</h2>
<div class="scroll"><table><tr><th>Car</th><th>Plate</th><th>Category</th><th>Price/day</th><th>Status</th><th></th></tr>
<?php foreach ($cars as $c): ?>
<tr><td><?= e($c['brand'] . ' ' . $c['model'] . ' ' . $c['year']) ?></td><td><?= e($c['plate']) ?></td><td><?= e($c['category']) ?></td>
<td><?= money($c['price_per_day']) ?></td><td><span class="st <?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
<td><a class="btn small" href="?edit=<?= $c['id'] ?>">Edit</a>
<form method="post" style="display:inline" onsubmit="return confirm('Delete this car?')"><?= csrf_field() ?><input type="hidden" name="delete" value="<?= $c['id'] ?>"><button class="btn small danger">Delete</button></form></td></tr>
<?php endforeach; ?></table></div>
<?php require '../includes/footer.php'; ?>
