<?php
require 'config.php'; $title = 'Register'; $err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? ''); $pass = $_POST['password'] ?? '';
    if ($name === '') $err[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Enter a valid email.';
    if (strlen($pass) < 6) $err[] = 'Password must be at least 6 characters.';
    if (!$err) {
        $c = $pdo->prepare("SELECT id FROM users WHERE email=?"); $c->execute([$email]);
        if ($c->fetch()) $err[] = 'That email is already registered.';
    }
    if (!$err) {
        $pdo->prepare("INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)")
            ->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
        flash('Account created. Please log in.'); header('Location: login.php'); exit;
    }
}
require 'includes/header.php';
?>
<div class="box"><h2>Create account</h2>
<?php foreach ($err as $x) echo '<div class="alert error">' . e($x) . '</div>'; ?>
<form method="post"><?= csrf_field() ?>
  <label>Full name<input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
  <label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
  <label>Phone<input name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></label>
  <label>Password<input type="password" name="password" required minlength="6"></label>
  <button class="btn">Register</button></form></div>
<?php require 'includes/footer.php'; ?>
