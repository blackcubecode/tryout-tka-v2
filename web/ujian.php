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

if ($sesi && $sesi['status'] === 'selesai') {
    header('Location: selesai.php?mapel=' . urlencode($mapel));
    exit;
}

if (!$sesi) {
    $mulai = new DateTime('now');
    $batas = (clone $mulai)->modify('+' . DURASI_MENIT . ' minutes');

    if ($mapel === 'Bahasa Indonesia') {
        $st = db()->prepare("SELECT selesai FROM sesi WHERE peserta_id=? AND mapel='Matematika' AND status='selesai'");
        $st->execute([$p['id']]);
        $selesai_mtk = $st->fetchColumn();
        if (!$selesai_mtk) {
            $_SESSION['pesan'] = 'Selesaikan Matematika terlebih dahulu.';
            header('Location: pilih_mapel.php');
            exit;
        }
        $boleh_mulai = (new DateTime($selesai_mtk))->modify('+' . JEDA_MENIT . ' minutes');
        if ($mulai < $boleh_mulai) {
            $_SESSION['pesan'] = 'Tunggu jeda ' . JEDA_MENIT . ' menit sebelum mulai.';
            header('Location: pilih_mapel.php');
            exit;
        }
    }

    $st = db()->prepare(
        'INSERT INTO sesi (peserta_id, mapel, mulai, batas_waktu, status, ip_address)
         VALUES (?, ?, ?, ?, "aktif", ?)'
    );
    $st->execute([
        $p['id'], $mapel,
        $mulai->format('Y-m-d H:i:s'),
        $batas->format('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    $sesi_id = (int)db()->lastInsertId();

    $st = db()->prepare(
        'INSERT INTO jawaban (sesi_id, soal_id)
         SELECT ?, id FROM soal WHERE mapel = ? AND aktif = 1'
    );
    $st->execute([$sesi_id, $mapel]);

    log_aksi($p['id'], 'mulai_ujian', "mapel=$mapel sesi=$sesi_id");

    $st = db()->prepare('SELECT * FROM sesi WHERE id = ?');
    $st->execute([$sesi_id]);
    $sesi = $st->fetch();
}

$now   = new DateTime('now');
$batas = new DateTime($sesi['batas_waktu']);
if ($now >= $batas) {
    require __DIR__ . '/api/submit.php';
    exit;
}

$st = db()->prepare(
    'SELECT s.id, s.no_urut, s.topik, s.teks, s.opsi_a, s.opsi_b, s.opsi_c, s.opsi_d,
            j.pilihan
     FROM soal s
     JOIN jawaban j ON j.soal_id = s.id
     WHERE j.sesi_id = ? AND s.mapel = ?
     ORDER BY s.no_urut'
);
$st->execute([$sesi['id'], $mapel]);
$soal_list = $st->fetchAll();

$sisa_detik = max(0, $batas->getTimestamp() - $now->getTimestamp());
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($mapel) ?> — <?= e(APP_NAMA) ?></title>
<link rel="stylesheet" href="assets/css/ujian.css">
</head>
<body class="halaman-ujian"
      data-sesi="<?= (int)$sesi['id'] ?>"
      data-mapel="<?= e($mapel) ?>"
      data-sisa="<?= (int)$sisa_detik ?>">

<header class="topbar">
  <div class="kiri">
    <span class="label-mapel"><?= e($mapel) ?></span>
    <span class="peserta"><?= e($p['nama']) ?> · <?= e($p['no_peserta']) ?></span>
  </div>
  <div class="timer" id="timer">--:--</div>
  <button class="tombol-selesai" id="btn-selesai">Selesai</button>
</header>

<main class="area-ujian">
  <aside class="navigasi-soal">
    <h3>Navigasi Soal</h3>
    <div class="grid-nomor" id="grid-nomor">
      <?php foreach ($soal_list as $i => $s): ?>
        <button class="nomor <?= $s['pilihan'] ? 'terjawab' : '' ?>"
                data-nomor="<?= $i + 1 ?>"
                data-soal-id="<?= (int)$s['id'] ?>">
          <?= $i + 1 ?>
        </button>
      <?php endforeach; ?>
    </div>
    <p class="keterangan">
      <span class="kotak contoh-terjawab"></span> sudah dijawab
      <span class="kotak contoh-belum"></span> belum
    </p>
  </aside>

  <section class="panel-soal" id="panel-soal">
    <?php foreach ($soal_list as $i => $s): ?>
      <article class="soal" data-nomor="<?= $i + 1 ?>" data-soal-id="<?= (int)$s['id'] ?>"
               style="<?= $i === 0 ? '' : 'display:none' ?>">
        <div class="soal-header">
          <span class="nomor-soal">Soal <?= $i + 1 ?> / <?= count($soal_list) ?></span>
          <span class="topik"><?= e($s['topik']) ?></span>
        </div>
        <div class="teks-soal"><?= nl2br(e($s['teks'])) ?></div>
        <div class="opsi">
          <?php foreach (['A' => 'opsi_a', 'B' => 'opsi_b', 'C' => 'opsi_c', 'D' => 'opsi_d'] as $huruf => $kol): ?>
            <label class="pilihan <?= $s['pilihan'] === $huruf ? 'dipilih' : '' ?>">
              <input type="radio" name="soal_<?= $s['id'] ?>"
                     value="<?= $huruf ?>"
                     data-soal-id="<?= (int)$s['id'] ?>"
                     data-huruf="<?= $huruf ?>"
                     <?= $s['pilihan'] === $huruf ? 'checked' : '' ?>>
              <span class="huruf"><?= $huruf ?></span>
              <span class="isi"><?= e($s[$kol]) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </article>
    <?php endforeach; ?>

    <div class="navigasi-bawah">
      <button class="tombol-nav" id="btn-prev">‹ Sebelumnya</button>
      <span class="indikator" id="indikator">1 / <?= count($soal_list) ?></span>
      <button class="tombol-nav" id="btn-next">Berikutnya ›</button>
    </div>
  </section>
</main>

<div class="overlay-konfirmasi" id="overlay-selesai" style="display:none">
  <div class="kotak-konfirmasi">
    <h2>Selesaikan Ujian?</h2>
    <p>Anda telah menjawab <b id="jumlah-terjawab">0</b> dari <?= count($soal_list) ?> soal.</p>
    <p>Setelah menekan <b>Ya, Selesai</b>, jawaban tidak dapat diubah.</p>
    <div class="tombol-baris">
      <button class="tombol-batal" id="btn-batal">Batal</button>
      <button class="tombol-utama" id="btn-ya-selesai">Ya, Selesai</button>
    </div>
  </div>
</div>

<script src="assets/js/ujian.js"></script>
</body>
</html>