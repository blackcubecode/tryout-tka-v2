<?php
require __DIR__ . '/config.php';
$p = wajib_login();

$st = db()->prepare(
    'SELECT mapel, status, mulai, selesai, nilai, jumlah_benar
     FROM sesi WHERE peserta_id = ?'
);
$st->execute([$p['id']]);
$sesi = [];
foreach ($st->fetchAll() as $r) {
    $sesi[$r['mapel']] = $r;
}

$mapel_list = ['Matematika', 'Bahasa Indonesia'];

function status_mapel(array $sesi, string $mapel): array
{
    $s = $sesi[$mapel] ?? null;
    if (!$s) {
        return ['label' => 'Belum dikerjakan', 'boleh_mulai' => true, 'info' => ''];
    }
    if ($s['status'] === 'selesai') {
        return [
            'label' => 'Selesai',
            'boleh_mulai' => false,
            'info' => 'Nilai: ' . number_format((float)$s['nilai'], 1) .
                      ' (' . $s['jumlah_benar'] . '/30)',
        ];
    }
    if ($s['status'] === 'aktif') {
        return ['label' => 'Sedang berlangsung', 'boleh_mulai' => true,
                'info' => 'Klik untuk melanjutkan'];
    }
    return ['label' => $s['status'], 'boleh_mulai' => false, 'info' => ''];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pilih Mapel — <?= e(APP_NAMA) ?></title>
<link rel="stylesheet" href="assets/css/ujian.css">
</head>
<body class="halaman-pilih">
<div class="kotak-pilih">
  <header>
    <h1>Halo, <?= e($p['nama']) ?></h1>
    <p class="sub">No. Peserta <b><?= e($p['no_peserta']) ?></b> — Kelas <?= e($p['kelas']) ?></p>
  </header>

  <p class="instruksi">Pilih mata pelajaran yang akan dikerjakan:</p>

  <div class="daftar-mapel">
    <?php foreach ($mapel_list as $m): ?>
      <?php $info = status_mapel($sesi, $m); ?>
      <div class="kartu-mapel <?= $info['boleh_mulai'] ? '' : 'terkunci' ?>">
        <h2><?= e($m) ?></h2>
        <p class="info-mapel">30 soal · <?= DURASI_MENIT ?> menit</p>
        <p class="status"><?= e($info['label']) ?></p>
        <?php if ($info['info']): ?>
          <p class="detail"><?= e($info['info']) ?></p>
        <?php endif; ?>

        <?php if ($info['boleh_mulai']): ?>
          <a class="tombol-utama" href="ujian.php?mapel=<?= urlencode($m) ?>">
            <?= ($sesi[$m]['status'] ?? '') === 'aktif' ? 'Lanjutkan' : 'Mulai' ?>
          </a>
        <?php else: ?>
          <button class="tombol-utama" disabled>Selesai</button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <footer>
    <a href="logout.php" class="tautan-keluar">Keluar</a>
  </footer>
</div>
</body>
</html>