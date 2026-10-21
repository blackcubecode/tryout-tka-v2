<?php
require __DIR__ . '/config_admin.php';

if (admin_login()) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login Admin — <?= e(APP_NAMA) ?></title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="halaman-login">
<div class="kotak-login">
  <h1>Dashboard Admin</h1>
  <p class="sub"><?= e(APP_NAMA) ?> — <?= e(APP_SEKOLAH) ?></p>

  <?php if ($error): ?>
    <div class="pesan-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" action="proses_login_admin.php" autocomplete="off">
    <label>Username</label>
    <input type="text" name="username" required autofocus>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit" class="tombol-utama">Masuk</button>
  </form>

  <p class="catatan">Default: admin / admin123 — ganti setelah login pertama.</p>
</div>
</body>
</html>
