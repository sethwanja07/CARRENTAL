<?php
// ---- Database settings (edit if your MySQL differs) ----
$db_host = 'localhost';
$db_name = 'car_rental';
$db_user = 'root';
$db_pass = '';          // XAMPP default is empty

session_start();

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('Database connection failed. Did you import database.sql? ' . htmlspecialchars($ex->getMessage()));
}

// Base URL (works from any folder name, also inside /admin)
$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (basename($dir) === 'admin') $dir = dirname($dir);
define('BASE', rtrim($dir, '/') . '/');
define('CURRENCY', 'RWF');

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return CURRENCY . ' ' . number_format((float)$n, 0); }
function logged_in() { return isset($_SESSION['user']); }
function require_login() { if (!logged_in()) { header('Location: ' . BASE . 'login.php'); exit; } }
function require_admin() {
    if (!logged_in() || $_SESSION['user']['role'] !== 'admin') { header('Location: ' . BASE . 'login.php'); exit; }
}
function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('Invalid form token. Go back and retry.');
}
function flash($msg, $type = 'ok') { $_SESSION['flash'] = [$msg, $type]; }
function valid_date($d) { $x = DateTime::createFromFormat('Y-m-d', (string)$d); return $x && $x->format('Y-m-d') === $d ? $x : false; }
function car_img($c) {
    if (!empty($c['image'])) return '<img src="' . BASE . 'uploads/' . e($c['image']) . '" alt="' . e($c['brand'] . ' ' . $c['model']) . '">';
    return '<div class="noimg">🚗</div>';
}
function car_is_free($pdo, $car_id, $start, $end) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE car_id=? AND status IN ('pending','approved') AND start_date<=? AND end_date>=?");
    $st->execute([$car_id, $end, $start]);
    return $st->fetchColumn() == 0;
}

// ---- Payment & SMS settings (edit these) ----
define('BANK_NAME', 'Bank of Kigali');
define('BANK_ACCOUNT', '00012-3456789-01 (DriveEasy Ltd)');
define('MTN_NUMBER', '0788 000 000');      // company MTN MoMo number (placeholder)
define('AIRTEL_NUMBER', '0732 000 000');   // company Airtel Money number (placeholder)

// SMS via Africa's Talking. Keep false to only log SMS in the database (demo mode).
define('SMS_ENABLED', false);
define('SMS_USERNAME', 'sandbox');
define('SMS_API_KEY', '');
define('SMS_SENDER', '');
define('SMS_API_URL', 'https://api.sandbox.africastalking.com/version1/messaging'); // live: https://api.africastalking.com/version1/messaging

function methods() { return ['bank' => 'Bank transfer', 'mtn' => 'MTN Mobile Money', 'airtel' => 'Airtel Money']; }
function method_label($m) { return methods()[$m] ?? $m; }

function notify($pdo, $uid, $msg, $link = null) {
    $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")->execute([$uid, $msg, $link]);
}
function notify_admins($pdo, $msg, $link = null, $sms = false) {
    foreach ($pdo->query("SELECT id, phone FROM users WHERE role='admin'")->fetchAll() as $a) {
        notify($pdo, $a['id'], $msg, $link);
        if ($sms) send_sms($pdo, $a['id'], $a['phone'], 'DriveEasy: ' . $msg);
    }
}
function phone_rw($p) {
    $d = preg_replace('/\D/', '', (string)$p);
    if (strpos($d, '250') === 0) return '+' . $d;
    if (strpos($d, '0') === 0) return '+250' . substr($d, 1);
    return $d === '' ? '' : '+250' . $d;
}
function send_sms($pdo, $user_id, $phone, $msg) {
    $to = phone_rw($phone); $msg = mb_substr($msg, 0, 450);
    if ($to === '') { $status = 'failed'; $resp = 'No phone number'; }
    elseif (SMS_ENABLED && SMS_API_KEY !== '' && function_exists('curl_init')) {
        $f = ['username' => SMS_USERNAME, 'to' => $to, 'message' => $msg];
        if (SMS_SENDER !== '') $f['from'] = SMS_SENDER;
        $ch = curl_init(SMS_API_URL);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($f),
            CURLOPT_HTTPHEADER => ['apiKey: ' . SMS_API_KEY, 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        $out = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
        $ok = $out !== false && $code >= 200 && $code < 300;
        $status = $ok ? 'sent' : 'failed'; $resp = substr($ok ? $out : ($err ?: "HTTP $code"), 0, 250);
    } else { $status = 'simulated'; $resp = 'SMS gateway disabled (demo mode) - see config.php'; }
    $pdo->prepare("INSERT INTO sms_log (user_id,phone,message,status,response) VALUES (?,?,?,?,?)")->execute([$user_id, $to ?: $phone, $msg, $status, $resp]);
    return $status;
}
