<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['konfirmasi']) && $_POST['konfirmasi'] === 'RESET') {
        db()->exec("DELETE FROM jawaban");
        db()->exec("DELETE FROM sesi");
        db()->exec("DELETE FROM log_aktivitas");
        db()->exec("ALTER TABLE jawaban AUTO_INCREMENT = 1");
        db()->exec("ALTER TABLE sesi AUTO_INCREMENT = 1");
        $pesan = '<div class="pesan-sukses">Data ujian berhasil direset. Siswa dapat mengerjakan ulang dari awal.</div>';
    } else {
        $pesan = '<div class="pesan-error">Ketik "RESET" (huruf besar) untuk konfirmasi.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Pelaksanaan - Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Reset Pelaksanaan Ujian</span>
  </div>
</header>
<main class="konten-admin">
  <h2>Reset Pelaksanaan Ujian</h2>
  <?= $pesan ?>

  <div class="kotak-konfirmasi" style="text-align:left; max-width:600px;">
    <p style="color:#e74c3c; font-weight:bold;">⚠️ PERINGATAN KERAS</p>
    <p>Tombol di bawah ini akan <b>menghapus permanen</b>:</p>
    <ul>
      <li>Semua jawaban siswa</li>
      <li>Semua sesi ujian (Matematika & B.Indonesia)</li>
      <li>Semua log aktivitas</li>
    </ul>
    <p><b>Data Peserta dan Soal TIDAK akan terhapus.</b></p>
    <p>Gunakan fitur ini jika Anda ingin mengulang ujian dari awal.</p>

    <form method="post" style="margin-top:20px;">
      <label>Ketik <b>RESET</b> untuk konfirmasi:</label>
      <input type="text" name="konfirmasi" required style="width:100%; padding:10px; margin:10px 0; border:2px solid #e74c3c; border-radius:8px;">
      <button type="submit" class="btn-kecil btn-merah" style="padding:12px 24px; font-size:15px;">🗑 Hapus Semua Data Ujian</button>
    </form>
  </div>
</main>
</body>
</html>
