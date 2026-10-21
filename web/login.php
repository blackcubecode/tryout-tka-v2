<?php
require __DIR__ . '/config.php';

if (peserta_login()) {
    header('Location: pilih_mapel.php');
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
<title>Login — <?= e(APP_NAMA) ?></title>
<link rel="stylesheet" href="assets/css/ujian.css">
</head>
<body class="halaman-login">
<div class="kotak-login">
  <h1><?= e(APP_NAMA) ?></h1>
  <p class="sub"><?= e(APP_SEKOLAH) ?> — <?= e(APP_TAHUN) ?></p>

  <?php if ($error): ?>
    <div class="pesan-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" action="proses_login.php" autocomplete="off">
    <label>Nomor Peserta</label>
    <input type="text" name="no_peserta" maxlength="10" required
           inputmode="numeric" pattern="[0-9]+" placeholder="2601001" autofocus>

    <label>Token (6 karakter)</label>
    <input type="text" name="token" maxlength="6" required
           pattern="[A-Z0-9]{6}" style="text-transform:uppercase"
           placeholder="A1B2C3">

    <button type="submit" class="tombol-utama">Masuk</button>
  </form>

  <p class="catatan">Lihat kartu peserta Anda untuk nomor dan token.</p>
</div>
</body>
</html>