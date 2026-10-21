<?php
require __DIR__ . '/config.php';
$p = wajib_login();

$mapel = $_GET['mapel'] ?? '';
if (!in_array($mapel, ['Matematika', 'Bahasa Indonesia'], true)) {
    header('Location: pilih_mapel.php');
    exit;
}

$st = db()->prepare('SELECT * FROM sesi WHERE peserta_id = ? AND mapel = ? LIMIT 1');
$st->execute([$p['id'], $mapel]);
$sesi = $st->fetch();

if (!$sesi || $sesi['status'] !== 'selesai') {
    header('Location: pilih_mapel.php');
    exit;
}

$st = db()->prepare('SELECT mapel, nilai, status FROM sesi WHERE peserta_id = ?');
$st->execute([$p['id']]);
$semua = [];
foreach ($st->fetchAll() as $r) $semua[$r['mapel']] = $r;

$mtk_selesai   = ($semua['Matematika']['status'] ?? '') === 'selesai';
$bindo_selesai = ($semua['Bahasa Indonesia']['status'] ?? '') === 'selesai';
$semua_selesai = $mtk_selesai && $bindo_selesai;

$rata = null;
if ($semua_selesai) {
    $rata = round((($semua['Matematika']['nilai'] + $semua['Bahasa Indonesia']['nilai']) / 2), 2);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Selesai — <?= e(APP_NAMA) ?></title>
<link rel="stylesheet" href="assets/css/ujian.css">
</head>
<body class="halaman-selesai">
<div class="kotak-selesai">
  <h1>Selesai</h1>
  <p class="sub"><?= e($mapel) ?> — <?= e($p['nama']) ?></p>

  <div class="kartu-nilai">
    <div class="nilai-besar"><?= number_format((float)$sesi['nilai'], 1) ?></div>
    <div class="detail-nilai">Benar <?= (int)$sesi['jumlah_benar'] ?> dari 30 soal</div>
    <?php if ((int)$sesi['waktu_habis'] === 1): ?>
      <div class="info-waktu">Waktu habis, jawaban tersimpan otomatis</div>
    <?php endif; ?>
  </div>

  <?php if ($semua_selesai): ?>
    <div class="kartu-nilai rata-rata">
      <div class="label-rata">Nilai Rata-rata</div>
      <div class="nilai-besar"><?= number_format($rata, 1) ?></div>
      <div class="detail-nilai">
        MTK: <?= number_format((float)$semua['Matematika']['nilai'], 1) ?> ·
        B.Indonesia: <?= number_format((float)$semua['Bahasa Indonesia']['nilai'], 1) ?>
      </div>
    </div>
  <?php endif; ?>

  <footer>
    <a href="pilih_mapel.php" class="tautan">Kembali ke daftar mapel</a>
    <a href="logout.php" class="tautan keluar">Keluar</a>
  </footer>
</div>
</body>
</html>
