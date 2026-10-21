<?php
require __DIR__ . '/config_admin.php';
$admin = wajib_admin();
$mapel_pilih = $_GET['mapel'] ?? 'Matematika';

// Fungsi Analisis
function hitung_analisis($mapel) {
    $stmt = db()->prepare("SELECT id, no_urut, kunci, topik, level, teks FROM soal WHERE mapel = ? ORDER BY no_urut");
    $stmt->execute([$mapel]);
    $soal = $stmt->fetchAll();

    $stmt = db()->prepare("SELECT s.id, p.nama, p.no_peserta FROM sesi s JOIN peserta p ON p.id = s.peserta_id WHERE s.mapel = ? AND s.status = 'selesai'");
    $stmt->execute([$mapel]);
    $sesi = $stmt->fetchAll();

    if (empty($soal) || empty($sesi)) return null;

    $sesi_ids = array_column($sesi, 'id');
    $in = str_repeat('?,', count($sesi_ids) - 1) . '?';
    
    $stmt = db()->prepare("SELECT sesi_id, soal_id, pilihan, benar FROM jawaban WHERE sesi_id IN ($in)");
    $stmt->execute($sesi_ids);
    $jawaban = $stmt->fetchAll();

    $matriks = [];
    $distraktor = [];
    foreach ($sesi as $s) {
        foreach ($soal as $q) {
            $matriks[$s['id']][$q['id']] = 0;
            $distraktor[$q['id']] = ['A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'Kosong'=>0];
        }
    }
    foreach ($jawaban as $j) {
        if (isset($matriks[$j['sesi_id']][$j['soal_id']])) {
            $matriks[$j['sesi_id']][$j['soal_id']] = $j['benar'];
            $p = $j['pilihan'] ?: 'Kosong';
            if (isset($distraktor[$j['soal_id']][$p])) $distraktor[$j['soal_id']][$p]++;
        }
    }

    $n = count($sesi);
    $total_skor = [];
    foreach ($matriks as $sid => $row) $total_skor[$sid] = array_sum($row);

    $mean_total = array_sum($total_skor) / $n;
    $var_total = 0;
    foreach ($total_skor as $val) $var_total += pow($val - $mean_total, 2);
    $var_total = $n > 1 ? $var_total / ($n - 1) : 0;

    $pq_sum = 0;
    $hasil_soal = [];

    foreach ($soal as $q) {
        $benar_count = 0;
        $skor_soal = [];
        $skor_lain = [];
        
        foreach ($sesi as $s) {
            $sid = $s['id'];
            $val = $matriks[$sid][$q['id']];
            if ($val) $benar_count++;
            $skor_soal[] = $val;
            $skor_lain[] = $total_skor[$sid] - $val;
        }

        $p_val = $benar_count / $n;
        $q_val = 1 - $p_val;
        $pq_sum += ($p_val * $q_val);

        $d_beda = 0;
        if ($n > 2 && $var_total > 0) {
            $mean_soal = array_sum($skor_soal) / $n;
            $mean_lain = array_sum($skor_lain) / $n;
            $cov = 0;
            for ($i = 0; $i < $n; $i++) {
                $cov += ($skor_soal[$i] - $mean_soal) * ($skor_lain[$i] - $mean_lain);
            }
            $cov /= ($n - 1);
            $std_soal = sqrt(array_sum(array_map(function($x) use ($mean_soal) { return pow($x - $mean_soal, 2); }, $skor_soal)) / ($n - 1));
            $std_lain = sqrt(array_sum(array_map(function($x) use ($mean_lain) { return pow($x - $mean_lain, 2); }, $skor_lain)) / ($n - 1));
            if ($std_soal > 0 && $std_lain > 0) $d_beda = $cov / ($std_soal * $std_lain);
        }

        $hasil_soal[] = [
            'no' => $q['no_urut'],
            'topik' => $q['topik'],
            'level' => $q['level'],
            'kunci' => $q['kunci'],
            'benar' => $benar_count,
            'p' => $p_val,
            'd' => $d_beda,
            'distraktor' => $distraktor[$q['id']]
        ];
    }

    $k = count($soal);
    $kr20 = 0;
    if ($var_total > 0 && $k > 1) {
        $kr20 = ($k / ($k - 1)) * (1 - ($pq_sum / $var_total));
    }

    return ['n' => $n, 'soal' => $hasil_soal, 'kr20' => $kr20];
}

$data = hitung_analisis($mapel_pilih);

// Tentukan kategori KR-20
$kategori_kr = 'Tidak cukup data';
if ($data) {
    if ($data['kr20'] < 0.50) $kategori_kr = 'Rendah';
    elseif ($data['kr20'] < 0.70) $kategori_kr = 'Cukup';
    elseif ($data['kr20'] < 0.85) $kategori_kr = 'Baik';
    else $kategori_kr = 'Sangat Baik';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Analisis Hasil - Dashboard Admin</title>
<link rel="stylesheet" href="assets/admin.css">
<style>
  .tabel-analisis th, .tabel-analisis td { font-size: 13px; padding: 8px; }
  .badge-sulit { background:#f8d7da; color:#721c24; }
  .badge-sedang { background:#fff3cd; color:#856404; }
  .badge-mudah { background:#d4edda; color:#155724; }
  .badge-jelek { background:#f8d7da; color:#721c24; }
  .badge-baik { background:#d4edda; color:#155724; }
</style>
</head>
<body>
<header class="topbar-admin">
  <div class="kiri">
    <a href="index.php" class="tautan-back">← Dashboard</a>
    <span class="judul">Analisis Hasil Ujian</span>
  </div>
</header>
<main class="konten-admin">
  <div class="toolbar">
    <form method="get" class="filter-form">
      <label>Pilih Mapel:</label>
      <select name="mapel" onchange="this.form.submit()">
        <option value="Matematika" <?= $mapel_pilih == 'Matematika' ? 'selected' : '' ?>>Matematika</option>
        <option value="Bahasa Indonesia" <?= $mapel_pilih == 'Bahasa Indonesia' ? 'selected' : '' ?>>Bahasa Indonesia</option>
      </select>
    </form>
  </div>

  <?php if (!$data): ?>
    <div class="pesan-error">Belum ada data sesi selesai untuk mapel ini. Lakukan ujian terlebih dahulu.</div>
  <?php else: ?>
    <div class="statistik">
      <div class="kartu-stat">
        <div class="angka"><?= $data['n'] ?></div>
        <div class="label">Peserta Ujian</div>
      </div>
      <div class="kartu-stat biru">
        <div class="angka"><?= number_format($data['kr20'], 3) ?></div>
        <div class="label">Reliabilitas (KR-20)</div>
      </div>
      <div class="kartu-stat hijau">
        <div class="angka"><?= $kategori_kr ?></div>
        <div class="label">Kategori Reliabilitas</div>
      </div>
    </div>

    <h2>Detail Per Soal</h2>
    <div class="tabel-wrapper">
      <table class="tabel-data tabel-analisis">
        <thead>
          <tr>
            <th>No</th>
            <th>Topik</th>
            <th>Kunci</th>
            <th>Benar</th>
            <th>p-value</th>
            <th>Kesukaran</th>
            <th>Daya Beda</th>
            <th>Klasifikasi</th>
            <th>Pilihan Siswa (A/B/C/D)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['soal'] as $s): ?>
            <tr>
              <td><b><?= $s['no'] ?></b></td>
              <td><?= e($s['topik']) ?></td>
              <td style="text-align:center; font-weight:bold; color:#1a4f8b;"><?= $s['kunci'] ?></td>
              <td><?= $s['benar'] ?> / <?= $data['n'] ?></td>
              <td><?= number_format($s['p'], 2) ?></td>
              <td>
                <?php if ($s['p'] < 0.3): ?>
                  <span class="badge badge-sulit">Sulit</span>
                <?php elseif ($s['p'] < 0.7): ?>
                  <span class="badge badge-sedang">Sedang</span>
                <?php else: ?>
                  <span class="badge badge-mudah">Mudah</span>
                <?php endif; ?>
              </td>
              <td><?= number_format($s['d'], 2) ?></td>
              <td>
                <?php if ($s['d'] < 0.2): ?>
                  <span class="badge badge-jelek">Jelek</span>
                <?php elseif ($s['d'] < 0.3): ?>
                  <span class="badge badge-sedang">Cukup</span>
                <?php elseif ($s['d'] < 0.4): ?>
                  <span class="badge badge-baik">Baik</span>
                <?php else: ?>
                  <span class="badge badge-mudah">Sangat Baik</span>
                <?php endif; ?>
              </td>
              <td style="font-size:12px; color:#666;">
                A: <?= $s['distraktor']['A'] ?> | 
                B: <?= $s['distraktor']['B'] ?> | 
                C: <?= $s['distraktor']['C'] ?> | 
                D: <?= $s['distraktor']['D'] ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
