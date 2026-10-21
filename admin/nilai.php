<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();

$filter_kelas = trim($_GET['kelas'] ?? '');
$filter_mapel = trim($_GET['mapel'] ?? '');

// Ambil daftar kelas unik
$kelas_list = db()->query("SELECT DISTINCT kelas FROM peserta ORDER BY kelas")->fetchAll(PDO::FETCH_COLUMN);

// Query rekap
$sql = 'SELECT no_peserta, nama, kelas, nilai_mtk, nilai_bindo, rata_rata FROM v_rekap_nilai';
$where = [];
$params = [];
if ($filter_kelas !== '') {
    $where[] = 'kelas = ?';
    $params[] = $filter_kelas;
}
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY kelas, nama';

$st = db()->prepare($sql);
$st->execute($params);
$data = $st->fetchAll();

// Statistik ringkas
$nilai_all = [];
foreach ($data as $r) {
    if ($r['nilai_mtk'] !== null) $nilai_all[] = (float)$r['nilai_mtk'];
    if ($r['nilai_bindo'] !== null) $nilai_all[] = (float)$r['nilai_bindo'];
}
$rata_keseluruhan = $nilai_all ? round(array_sum($nilai_all) / count($nilai_all), 2) : 0;
$nilai_tertinggi  = $nilai_all ? max($nilai_all) : 0;
$nilai_terendah   = $nilai_all ? min($nilai_all) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nilai — Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Rekap Nilai</span>
  </div>
  <div class="kanan">
    <span>Halo, <b><?= e($admin['username']) ?></b></span>
    <a href="logout_admin.php" class="tautan-logout">Keluar</a>
  </div>
</header>

<main class="konten-admin">
  <div class="toolbar">
    <form method="get" class="filter-form">
      <label>Kelas:</label>
      <select name="kelas" onchange="this.form.submit()">
        <option value="">Semua</option>
        <?php foreach ($kelas_list as $k): ?>
          <option value="<?= e($k) ?>" <?= $k === $filter_kelas ? 'selected' : '' ?>>
            <?= e($k) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
    <a href="export_excel.php?kelas=<?= urlencode($filter_kelas) ?>" class="tombol-hijau">
      ⬇ Export Excel
    </a>
  </div>

  <div class="statistik">
    <div class="kartu-stat">
      <div class="angka"><?= count($data) ?></div>
      <div class="label">Siswa</div>
    </div>
    <div class="kartu-stat biru">
      <div class="angka"><?= number_format($rata_keseluruhan, 1) ?></div>
      <div class="label">Rata-rata</div>
    </div>
    <div class="kartu-stat hijau">
      <div class="angka"><?= number_format($nilai_tertinggi, 1) ?></div>
      <div class="label">Tertinggi</div>
    </div>
    <div class="kartu-stat merah">
      <div class="angka"><?= number_format($nilai_terendah, 1) ?></div>
      <div class="label">Terendah</div>
    </div>
  </div>

  <div class="tabel-wrapper">
    <table class="tabel-data">
      <thead>
        <tr>
          <th>No</th>
          <th>No. Peserta</th>
          <th>Nama</th>
          <th>Kelas</th>
          <th>MTK</th>
          <th>B.Indonesia</th>
          <th>Rata-rata</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
          <tr><td colspan="7" style="text-align:center;padding:40px;color:#888">
            Belum ada data nilai.
          </td></tr>
        <?php else: ?>
          <?php foreach ($data as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($r['no_peserta']) ?></td>
              <td><?= e($r['nama']) ?></td>
              <td><?= e($r['kelas']) ?></td>
              <td class="nilai"><?= $r['nilai_mtk'] !== null ? number_format((float)$r['nilai_mtk'], 1) : '—' ?></td>
              <td class="nilai"><?= $r['nilai_bindo'] !== null ? number_format((float)$r['nilai_bindo'], 1) : '—' ?></td>
              <td class="nilai tebal"><?= $r['rata_rata'] !== null ? number_format((float)$r['rata_rata'], 2) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
