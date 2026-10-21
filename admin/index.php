<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();

$total_peserta = (int) db()->query('SELECT COUNT(*) FROM peserta')->fetchColumn();
$total_soal    = (int) db()->query('SELECT COUNT(*) FROM soal')->fetchColumn();
$total_selesai = (int) db()->query("SELECT COUNT(*) FROM sesi WHERE status='selesai'")->fetchColumn();
$total_aktif   = (int) db()->query("SELECT COUNT(*) FROM sesi WHERE status='aktif'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Admin - <?= e(APP_NAMA) ?></title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <span class="judul">Dashboard Admin</span>
    <span class="sub"><?= e(APP_SEKOLAH) ?> - <?= e(APP_TAHUN) ?></span>
  </div>
  <div class="kanan">
    <span>Halo, <b><?= e($admin['username']) ?></b></span>
    <a href="logout_admin.php" class="tautan-logout">Keluar</a>
  </div>
</header>

<main class="konten-admin">
  <h2>Selamat Datang</h2>

  <div class="statistik">
    <div class="kartu-stat">
      <div class="angka"><?= $total_peserta ?></div>
      <div class="label">Peserta</div>
    </div>
    <div class="kartu-stat">
      <div class="angka"><?= $total_soal ?></div>
      <div class="label">Soal</div>
    </div>
    <div class="kartu-stat biru">
      <div class="angka"><?= $total_selesai ?></div>
      <div class="label">Sesi Selesai</div>
    </div>
    <div class="kartu-stat kuning">
      <div class="angka"><?= $total_aktif ?></div>
      <div class="label">Sedang Berlangsung</div>
    </div>
  </div>

  <h2>Menu</h2>
  <div class="menu-grid">
    <a href="nilai.php" class="menu-kartu">
      <div class="ikon">📊</div>
      <div class="judul-menu">Lihat Nilai</div>
      <div class="deskripsi">Rekap nilai, filter kelas, export Excel</div>
    </a>
    <a href="analisis.php" class="menu-kartu">
      <div class="ikon">📈</div>
      <div class="judul-menu">Analisis Hasil</div>
      <div class="deskripsi">KR-20, tingkat kesukaran, distraktor</div>
    </a>
    <a href="siswa.php" class="menu-kartu">
      <div class="ikon">👥</div>
      <div class="judul-menu">Edit Siswa</div>
      <div class="deskripsi">Ubah nama/kelas, aktif/nonaktif, hapus</div>
    </a>
    <a href="upload_soal.php" class="menu-kartu">
      <div class="ikon">📤</div>
      <div class="judul-menu">Upload Soal</div>
      <div class="deskripsi">Ganti soal via file CSV/Excel</div>
    </a>
    <a href="soal.php" class="menu-kartu">
      <div class="ikon">📝</div>
      <div class="judul-menu">Edit Soal Manual</div>
      <div class="deskripsi">Ubah teks, opsi, kunci satu per satu</div>
    </a>
    <a href="token.php" class="menu-kartu">
      <div class="ikon">🔑</div>
      <div class="judul-menu">Kelola Token</div>
      <div class="deskripsi">Reset token per siswa atau semua siswa</div>
    </a>
    <a href="reset_ujian.php" class="menu-kartu" style="border: 2px solid #e74c3c;">
      <div class="ikon">⚠️</div>
      <div class="judul-menu" style="color:#e74c3c">Reset Pelaksanaan</div>
      <div class="deskripsi">Hapus semua jawaban & sesi siswa</div>
    </a>
  </div>
</main>
</body>
</html>
