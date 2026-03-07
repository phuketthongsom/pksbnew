<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/helpers.php';

auth_start();
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'en';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (try_login($user, $pass)) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
if (is_logged_in()) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PKSB Admin Login</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .login-logo { text-align:center; margin-bottom:24px; }
    .login-logo img { height:64px; margin:0 auto 10px; display:block; }
    .login-logo h2 { font-size:1rem; color:#555; }
  </style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <img src="../assets/images/logo.png" alt="PKSB">
    <h2>Admin Panel</h2>
  </div>
  <?php if ($error): ?>
  <div class="alert alert-error"><?= esc($error) ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" autofocus autocomplete="username">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%">Login</button>
  </form>
  <p style="text-align:center;margin-top:16px;font-size:.82rem;color:#999">Default: admin / admin123</p>
</div>
</body>
</html>
