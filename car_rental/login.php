<?php
require 'config.php'; $title = 'Login'; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $st = $pdo->prepare("SELECT * FROM users WHERE email=?"); $st->execute([trim($_POST['email'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => $u['role']];
        header('Location: ' . ($u['role'] === 'admin' ? 'admin/index.php' : 'index.php')); exit;
    }
    $err = 'Wrong email or password.';
}
require 'includes/header.php';
?>
<div class="box"><h2>Login</h2>
<?php if ($err) echo '<div class="alert error">' . e($err) . '</div>'; ?>
<form method="post"><?= csrf_field() ?>
  <label>Email<input type="email" name="email" required></label>
  <label>Password<input type="password" name="password" required></label>
  <button class="btn">Login</button></form>
<p class="muted">Demo: admin@carrental.test or customer@carrental.test, password <b>admin123</b></p></div>
<?php require 'includes/footer.php'; ?>
